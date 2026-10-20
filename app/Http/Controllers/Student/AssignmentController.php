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
            $assignments = Assignment::with('subject')
                ->where('section_id', $sectionId)
                ->where('is_published', true)
                ->latest()
                ->get()
                ->map(function ($a) use ($user) {
                    $a->mySubmission = $a->submissionByStudent($user->id);
                    return $a;
                });
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
