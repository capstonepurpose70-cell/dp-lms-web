<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    private function sectionId($user)
    {
        return $user->studentEnrollment?->section?->id ?? $user->section_id;
    }

    public function index()
    {
        $user = auth()->user();
        $sectionId = $this->sectionId($user);

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
        $sectionId = $this->sectionId($user);

        abort_unless(
            $assignment->is_published && $assignment->section_id === $sectionId,
            403
        );

        $assignment->load('subject');
        $submission = $assignment->submissionByStudent($user->id);

        return view('student.quiz-show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = auth()->user();
        $sectionId = $this->sectionId($user);

        abort_unless(
            $assignment->is_published && $assignment->section_id === $sectionId,
            403
        );

        $request->validate([
            'remarks' => 'nullable|string',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        if (! $request->hasFile('file') && ! $request->filled('remarks')) {
            return back()->withErrors(['file' => 'Please attach a file or type your answer.']);
        }

        $submission = Submission::firstOrNew([
            'assignment_id' => $assignment->id,
            'user_id'       => $user->id,
        ]);

        // 🔒 Locked once graded — no re-submission allowed.
        if ($submission->exists && $submission->status === 'graded') {
            return back()->withErrors([
                'file' => 'This activity has already been graded. Submissions are locked.',
            ]);
        }

        if ($request->hasFile('file')) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->file_path = $request->file('file')
                ->store("submissions/{$assignment->id}/{$user->id}", 'public');
        }

        $submission->remarks      = $request->input('remarks', $submission->remarks);
        $submission->status       = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        return redirect()->route('student.quizzes')
            ->with('success', 'Answer submitted. Waiting for your teacher to grade it.');
    }
}