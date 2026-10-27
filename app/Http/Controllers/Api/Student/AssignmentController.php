<?php

namespace App\Http\Controllers\Api\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** GET /api/student/assignments — published quizzes/assignments for the student's section */
    public function index(Request $request)
    {
        $user      = $request->user();
        $sectionId = $user->studentSectionId();
        if (! $sectionId) {
            return response()->json([]);
        }

        $items = Assignment::with('subject')
            ->where('section_id', $sectionId)
            ->where('is_published', true)
            ->latest()
            ->get()
            ->map(function ($a) use ($user) {
                $sub = $a->submissionByStudent($user->id);
                return [
                    'id'           => $a->id,
                    'subject_id'   => $a->subject_id,
                    'title'        => $a->title,
                    'instructions' => $a->instructions,
                    'file_url'     => $a->file_path ? Storage::disk('public')->url($a->file_path) : null,
                    'due_date'     => $a->due_date?->toDateTimeString(),
                    'due_human'    => $a->due_date?->diffForHumans(),
                    'max_score'    => $a->max_score,
                    'is_overdue'   => $a->isOverdue(),
                    'subject_name' => $a->subject?->name,
                    'submitted'    => $sub !== null,
                    'status'       => $sub?->status,   // submitted | graded | returned | null
                    'score'        => $sub?->score,
                ];
            });

        return response()->json($items);
    }

    /** GET /api/student/assignments/{id} — one assignment + the student's own submission */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $a = Assignment::with('subject')
            ->where('section_id', $user->studentSectionId())
            ->where('is_published', true)
            ->findOrFail($id);

        $sub = $a->submissionByStudent($user->id);

        return response()->json([
            'id'           => $a->id,
            'title'        => $a->title,
            'instructions' => $a->instructions,
            'file_url'     => $a->file_path ? Storage::disk('public')->url($a->file_path) : null,
            'due_date'     => $a->due_date?->toDateTimeString(),
            'due_human'    => $a->due_date?->diffForHumans(),
            'max_score'    => $a->max_score,
            'is_overdue'   => $a->isOverdue(),
            'subject_name' => $a->subject?->name,
            'submission'   => $sub ? [
                'id'           => $sub->id,
                'file_url'     => $sub->file_path ? Storage::disk('public')->url($sub->file_path) : null,
                'remarks'      => $sub->remarks,   // the student's answer
                'feedback'     => $sub->feedback,  // the teacher's comment
                'score'        => $sub->score,
                'status'       => $sub->status,
                'submitted_at' => $sub->submitted_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    /** POST /api/student/assignments/{id}/submit — submit answer (file and/or typed text) */
    public function submit(Request $request, $id, SubmissionService $submissions)
    {
        $user = $request->user();

        $a = Assignment::where('section_id', $user->studentSectionId())
            ->where('is_published', true)
            ->findOrFail($id);

        $request->validate([
            'remarks' => 'nullable|string|max:20000',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        try {
            $submissions->submit($a, $user, $request->file('file'), $request->input('remarks'));
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Answer submitted. Waiting for your teacher to grade it.',
        ]);
    }
}
