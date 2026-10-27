<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\User;
use App\Services\ClassAttendanceService;

class TeacherDashboardController extends Controller
{
    public function index(ClassAttendanceService $classes)
    {
        $teacher    = auth()->user();
        $sectionIds = $teacher->teachingSectionIds();

        // Each class = subject + section (a subject taught in 3 sections is 3 classes)
        $classList = $classes->teacherClasses($teacher);

        $students = User::whereIn('id', User::studentIdsInSections($sectionIds))
            ->where('status', 'approved')
            ->with('section')
            ->orderBy('name')
            ->get();

        return view('teacher.dashboard', [
            'teacher'      => $teacher,
            'classList'    => $classList,
            'advisory'     => Section::where('adviser_id', $teacher->id)->where('is_active', true)
                                ->orderBy('name')->get(['id', 'name', 'grade_level']),
            'students'     => $students,
            'studentCount' => $students->count(),
            'subjectCount' => $classList->unique('subject_id')->count(),
            // Same sections as "My Students" (subject classes + advisory)
            'sectionCount' => $sectionIds->count(),
        ]);
    }
}
