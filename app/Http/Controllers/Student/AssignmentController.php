<?php

namespace App\Http\Controllers\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\SubmissionService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $sectionId = $user->studentSectionId();

        $assignments = collect();
        if ($sectionId) {
            // Own submission eager-loaded (not one query per quiz)
            $rank = ['todo' => 0, 'submitted' => 1, 'graded' => 2, 'missed' => 3];

            $assignments = Assignment::with(['subject', 'submissions' => fn ($q) => $q->where('user_id', $user->id)])
                ->where('section_id', $sectionId)
                ->where('is_published', true)
                ->latest()
                ->get()
                ->map(function ($a) {
                    $a->mySubmission = $a->submissions->first();
                    $a->state = $a->mySubmission
                        ? ($a->mySubmission->status === 'graded' ? 'graded' : 'submitted')
                        : ($a->isOverdue() ? 'missed' : 'todo');
                    return $a;
                })
                // To do first (nearest deadline first), then submitted, graded, missed
                ->sortBy(fn ($a) => sprintf('%d|%015d', $rank[$a->state], $a->due_date?->timestamp ?? 999999999999999))
                ->values();
        }

        return view('student.quizzes', compact('assignments'));
    }

    public function show(Assignment $assignment)
    {
        $user = auth()->user();

        abort_unless(SubmissionService::visibleTo($assignment, $user), 403);

        $assignment->load('subject');
        $submission = $assignment->submissionByStudent($user->id);

        return view('student.quiz-show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment, SubmissionService $submissions)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:20000',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        try {
            $submissions->submit($assignment, auth()->user(), $request->file('file'), $request->input('remarks'));
        } catch (ActionNotAllowed $e) {
            abort_if($e->status === 404, 403);
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('student.quizzes')
            ->with('success', 'Answer submitted. Waiting for your teacher to grade it.');
    }
}
