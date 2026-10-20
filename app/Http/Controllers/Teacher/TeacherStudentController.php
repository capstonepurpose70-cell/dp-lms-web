<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;

class TeacherStudentController extends Controller
{
    public function index()
    {
        // Subject sections + advisory section (dati subject lang)
        $students = User::whereIn('id', User::studentIdsInSections(auth()->user()->teachingSectionIds()))
            ->where('status', 'approved')
            ->with(['section'])
            ->orderBy('name')
            ->get();

        return view('teacher.students.index', compact('students'));
    }
}
