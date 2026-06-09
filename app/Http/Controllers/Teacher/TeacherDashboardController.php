<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherSubject;
use App\Models\User;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        // Get this teacher's assigned sections and subjects
        $assignments = TeacherSubject::with(['subject', 'section'])
            ->where('user_id', $teacher->id)
            ->get();

        $sectionIds = $assignments->pluck('section_id')->unique();

        // Get students in teacher's sections
        $students = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereIn('section_id', $sectionIds)
            ->with('section')
            ->orderBy('name')
            ->get();

        return view('teacher.dashboard', [
            'teacher'     => $teacher,
            'assignments' => $assignments,
            'sections'    => $assignments->groupBy('section_id'),
            'students'    => $students,
            'studentCount'=> $students->count(),
            'subjectCount'=> $assignments->unique('subject_id')->count(),
        ]);
    }
}