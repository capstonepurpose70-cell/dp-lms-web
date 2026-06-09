<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherSubject;
use App\Models\User;

class TeacherStudentController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        $sectionIds = TeacherSubject::where('user_id', $teacher->id)
            ->pluck('section_id')
            ->unique();

        $students = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereIn('section_id', $sectionIds)
            ->with(['section']) // grades.subject removed — add back once Grade model confirmed
            ->orderBy('name')
            ->get();

        return view('teacher.students.index', compact('students'));
    }
}