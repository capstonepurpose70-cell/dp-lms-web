<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Models\TeacherSubject;
use App\Models\SchoolYear;
use App\Services\AuditLogService;
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

    // POST /teacher/gradebook
    // Same logic as the mobile API saveGrade() so both stay identical.
    public function store(Request $request)
    {
        $request->validate([
            'student_id'           => 'required|exists:users,id',
            'subject_id'           => 'required|exists:subjects,id',
            'quarter'              => 'required|in:Q1,Q2,Q3,Q4',
            'written_works'        => 'nullable|numeric|min:0|max:100',
            'performance_tasks'    => 'nullable|numeric|min:0|max:100',
            'quarterly_assessment' => 'nullable|numeric|min:0|max:100',
        ]);

        $teacher = auth()->user();

        // Ensure teacher is assigned to this subject (same guard as mobile).
        $isAssigned = TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'You are not assigned to this subject.')->withInput();
        }

        // DepEd formula: WW 25% + PT 50% + QA 25%
        $ww = $request->written_works ?? 0;
        $pt = $request->performance_tasks ?? 0;
        $qa = $request->quarterly_assessment ?? 0;
        $finalGrade = round(($ww * 0.25) + ($pt * 0.50) + ($qa * 0.25), 2);

        Grade::updateOrCreate(
            [
                'user_id'    => $request->student_id,
                'subject_id' => $request->subject_id,
                'quarter'    => $request->quarter,
            ],
            [
                'written_works'        => $ww,
                'performance_tasks'    => $pt,
                'quarterly_assessment' => $qa,
                'final_grade'          => $finalGrade,
                'remarks'              => $finalGrade >= 75 ? 'Passed' : 'Failed',
                'school_year'          => SchoolYear::current()?->label
                                            ?? now()->year . '-' . (now()->year + 1),
            ]
        );

        AuditLogService::log(
            "Updated grade for student ID {$request->student_id}",
            'Gradebook',
            "Subject: {$request->subject_id} | Quarter: {$request->quarter} | Final: {$finalGrade}"
        );

        return back()->with('success', "Grade saved successfully. Final grade: {$finalGrade} ("
            . ($finalGrade >= 75 ? 'Passed' : 'Failed') . ').');
    }
}