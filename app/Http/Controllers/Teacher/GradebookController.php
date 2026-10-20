<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Models\TeacherSubject;
use App\Services\GradeService;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function index()
    {
        $teacher    = auth()->user();
        $subjectIds = TeacherSubject::where('user_id', $teacher->id)
                        ->pluck('subject_id');
        $sectionIds = TeacherSubject::where('user_id', $teacher->id)
                        ->pluck('section_id');

        $grades = Grade::with(['student', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->whereHas('student', fn($q) => $q->whereIn('section_id', $sectionIds))
            ->latest()
            ->paginate(20);

        // For the encode form (additive — does NOT affect the existing display).
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $students = User::where('role', 'student')
            ->whereIn('section_id', $sectionIds)
            ->with('section')
            ->orderBy('name')
            ->get();

        return view('teacher.gradebook', compact('grades', 'subjects', 'students'));
    }

    // POST /teacher/gradebook — same logic as the mobile API (GradeService).
    public function store(Request $request, GradeService $grades)
    {
        $data = $request->validate(GradeService::rules());

        try {
            $grade = $grades->save(auth()->user(), $data);
        } catch (ActionNotAllowed $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', "Grade saved successfully. Final grade: {$grade->final_grade} ("
            . ($grade->isPassed() ? 'Passed' : 'Failed') . ').');
    }
}