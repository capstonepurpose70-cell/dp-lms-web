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

        // Subject assignments (para sa "My Subjects" cards)
        $assignments = TeacherSubject::with(['subject', 'section'])
            ->where('user_id', $teacher->id)
            ->get();

        // Students: subject sections + advisory section (dati subject lang —
        // hindi nakikita ng adviser ang sarili niyang klase)
        $students = User::whereIn('id', User::studentIdsInSections($teacher->teachingSectionIds()))
            ->where('status', 'approved')
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
