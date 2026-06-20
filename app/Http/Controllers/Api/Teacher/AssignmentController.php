<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** GET /api/teacher/assignments — list this teacher's quizzes/assignments */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $items = Assignment::with(['subject', 'section'])
            ->withCount('submissions')
            ->where('user_id', $teacher->id)
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'                => $a->id,
                'title'             => $a->title,
                'instructions'      => $a->instructions,
                'file_url'          => $a->file_path ? Storage::disk('public')->url($a->file_path) : null,
                'due_date'          => $a->due_date?->toDateTimeString(),
                'due_human'         => $a->due_date?->diffForHumans(),
                'max_score'         => $a->max_score,
                'is_published'      => $a->is_published,
                'is_overdue'        => $a->isOverdue(),
                'subject_id'        => $a->subject_id,
                'section_id'        => $a->section_id,
                'subject_name'      => $a->subject?->name,
                'section_name'      => $a->section?->name,
                'submissions_count' => $a->submissions_count,
                'created_at'        => $a->created_at?->toDateTimeString(),
            ]);

        return response()->json($items);
    }

    /** POST /api/teacher/assignments — create a quiz/assignment (optional file) */
    public function store(Request $request)
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'subject_id'   => 'required|exists:subjects,id',
            'section_id'   => 'required|exists:sections,id',
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_date'     => 'nullable|date',
            'max_score'    => 'nullable|integer|min:1|max:1000',
            'is_published' => 'nullable|boolean',
            'file'         => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        // Make sure the teacher actually handles this subject + section
        $teaches = TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $validated['subject_id'])
            ->where('section_id', $validated['section_id'])
            ->exists();

        if (! $teaches) {
            return response()->json([
                'ok'      => false,
                'message' => 'You are not assigned to this subject/section.',
            ], 403);
        }

        $assignment = Assignment::create([
            'subject_id'   => $validated['subject_id'],
            'user_id'      => $teacher->id,
            'section_id'   => $validated['section_id'],
            'title'        => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_date'     => $validated['due_date'] ?? null,
            'max_score'    => $validated['max_score'] ?? 100,
            'is_published' => $request->boolean('is_published', true),
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store("assignments/{$assignment->id}", 'public');
            $assignment->update(['file_path' => $path]);
        }

        // 🔔 Push notification (FCM) to students in this section.
        if ($assignment->is_published) {
            $quizStudentIds = User::where('role', 'student')
                ->where('status', 'approved')
                ->where('section_id', $validated['section_id'])
                ->pluck('id')->all();
            app(PushNotificationService::class)->sendToUsers(
                $quizStudentIds,
                'New quiz: ' . $assignment->title,
                "{$teacher->name} posted a new quiz",
                ['type' => 'quiz', 'id' => $assignment->id],
            );
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Quiz/assignment created successfully.',
            'id'      => $assignment->id,
        ]);
    }

    /** GET /api/teacher/assignments/{id} — one assignment + its submissions */
    public function show(Request $request, $id)
    {
        $teacher = $request->user();

        $assignment = Assignment::with(['subject', 'section'])
            ->where('user_id', $teacher->id)
            ->findOrFail($id);

        $submissions = $assignment->submissions()
            ->with('student')
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'student_id'      => $s->user_id,
                'student_name'    => $s->student?->name,
                'file_url'        => $s->file_path ? Storage::disk('public')->url($s->file_path) : null,
                'remarks'         => $s->remarks,
                'score'           => $s->score,
                'status'          => $s->status,
                'submitted_at'    => $s->submitted_at?->toDateTimeString(),
                'submitted_human' => $s->submitted_at?->diffForHumans(),
            ]);

        return response()->json([
            'id'           => $assignment->id,
            'title'        => $assignment->title,
            'instructions' => $assignment->instructions,
            'file_url'     => $assignment->file_path ? Storage::disk('public')->url($assignment->file_path) : null,
            'due_date'     => $assignment->due_date?->toDateTimeString(),
            'max_score'    => $assignment->max_score,
            'is_published' => $assignment->is_published,
            'subject_name' => $assignment->subject?->name,
            'section_name' => $assignment->section?->name,
            'submissions'  => $submissions,
        ]);
    }

    /** POST /api/teacher/submissions/{id}/grade — grade a student submission */
    public function grade(Request $request, $id)
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'score'   => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $submission = Submission::with('assignment')->findOrFail($id);

        if ($submission->assignment?->user_id !== $teacher->id) {
            return response()->json(['ok' => false, 'message' => 'Not allowed.'], 403);
        }

        $submission->update([
            'score'   => $validated['score'],
            'remarks' => $validated['remarks'] ?? $submission->remarks,
            'status'  => 'graded',
        ]);

        return response()->json(['ok' => true, 'message' => 'Submission graded.']);
    }

    /** DELETE /api/teacher/assignments/{id} */
    public function destroy(Request $request, $id)
    {
        $teacher = $request->user();
        $assignment = Assignment::where('user_id', $teacher->id)->findOrFail($id);

        Storage::disk('public')->deleteDirectory("assignments/{$assignment->id}");
        Storage::disk('public')->deleteDirectory("submissions/{$assignment->id}");
        $assignment->delete();

        return response()->json(['ok' => true, 'message' => 'Assignment deleted.']);
    }
}