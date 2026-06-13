<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['subject', 'section'])
            ->withCount('submissions')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('teacher.assignments', compact('assignments'));
    }

    public function create()
    {
        // Subject + section pairs this teacher handles
        $pairs = TeacherSubject::with(['subject', 'section'])
            ->where('user_id', auth()->id())
            ->get()
            ->filter(fn($t) => $t->subject && $t->section)
            ->map(fn($t) => [
                'subject_id'   => $t->subject_id,
                'subject_name' => $t->subject->name,
                'section_id'   => $t->section_id,
                'section_name' => $t->section->name,
            ])
            ->unique(fn($p) => $p['subject_id'] . '-' . $p['section_id'])
            ->values();

        return view('teacher.assignment-create', compact('pairs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id'   => 'required|exists:subjects,id',
            'section_id'   => 'required|exists:sections,id',
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_date'     => 'nullable|date',
            'max_score'    => 'nullable|integer|min:1|max:1000',
            'is_published' => 'nullable|boolean',
            'file'         => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx',
        ]);

        $teaches = TeacherSubject::where('user_id', auth()->id())
            ->where('subject_id', $data['subject_id'])
            ->where('section_id', $data['section_id'])
            ->exists();

        if (! $teaches) {
            return back()->withInput()->withErrors([
                'subject_id' => 'You are not assigned to this subject/section.',
            ]);
        }

        $assignment = Assignment::create([
            'subject_id'   => $data['subject_id'],
            'user_id'      => auth()->id(),
            'section_id'   => $data['section_id'],
            'title'        => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'due_date'     => $data['due_date'] ?? null,
            'max_score'    => $data['max_score'] ?? 100,
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store("assignments/{$assignment->id}", 'public');
            $assignment->update(['file_path' => $path]);
        }

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Quiz/assignment created successfully.');
    }

    public function show(Assignment $assignment)
    {
        abort_unless($assignment->user_id === auth()->id(), 403);

        $assignment->load(['subject', 'section']);
        $submissions = $assignment->submissions()
            ->with('student')
            ->orderByDesc('submitted_at')
            ->get();

        return view('teacher.assignment-show', compact('assignment', 'submissions'));
    }

    public function grade(Request $request, Submission $submission)
    {
        abort_unless($submission->assignment?->user_id === auth()->id(), 403);

        $data = $request->validate([
            'score'   => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $submission->update([
            'score'   => $data['score'],
            'remarks' => $data['remarks'] ?? $submission->remarks,
            'status'  => 'graded',
        ]);

        return back()->with('success', 'Submission graded.');
    }

    public function destroy(Assignment $assignment)
    {
        abort_unless($assignment->user_id === auth()->id(), 403);

        Storage::disk('public')->deleteDirectory("assignments/{$assignment->id}");
        Storage::disk('public')->deleteDirectory("submissions/{$assignment->id}");
        $assignment->delete();

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Assignment deleted.');
    }
}