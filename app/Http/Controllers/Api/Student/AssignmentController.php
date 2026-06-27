<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** Resolve the student's section (enrollment-aware) */
    private function sectionId($user)
    {
        return $user->studentEnrollment?->section?->id ?? $user->section_id;
    }

    /** GET /api/student/assignments — published quizzes/assignments for the student's section */
    public function index(Request $request)
    {
        $user = $request->user();
        $sectionId = $this->sectionId($user);
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
        $sectionId = $this->sectionId($user);

        $a = Assignment::with('subject')
            ->where('section_id', $sectionId)
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
                'remarks'      => $sub->remarks,
                'score'        => $sub->score,
                'status'       => $sub->status,
                'submitted_at' => $sub->submitted_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    /** POST /api/student/assignments/{id}/submit — submit answer (file and/or typed text) */
    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $sectionId = $this->sectionId($user);

        $a = Assignment::where('section_id', $sectionId)
            ->where('is_published', true)
            ->findOrFail($id);

        $request->validate([
            'remarks' => 'nullable|string',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        if (! $request->hasFile('file') && ! $request->filled('remarks')) {
            return response()->json([
                'ok'      => false,
                'message' => 'Please attach a file or type your answer.',
            ], 422);
        }

        // One submission per student — re-submitting updates the existing one
        $submission = Submission::firstOrNew([
            'assignment_id' => $a->id,
            'user_id'       => $user->id,
        ]);

        // 🔒 Locked once graded — no re-submission allowed.
        if ($submission->exists && $submission->status === 'graded') {
            return response()->json([
                'ok'      => false,
                'message' => 'This activity has already been graded. Submissions are locked.',
            ], 422);
        }

        if ($request->hasFile('file')) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->file_path = $request->file('file')
                ->store("submissions/{$a->id}/{$user->id}", 'public');
        }

        $submission->remarks      = $request->input('remarks', $submission->remarks);
        $submission->status       = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Answer submitted. Waiting for your teacher to grade it.',
        ]);
    }
}