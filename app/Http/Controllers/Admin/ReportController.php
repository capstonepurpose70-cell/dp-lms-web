<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\StudentEnrollment;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $totalStudents  = User::where('role', 'student')->count();
        $approvedStudents = User::where('role', 'student')->where('status', 'approved')->count();
        $pendingStudents  = User::where('role', 'student')->where('status', 'pending')->count();
        $rejectedStudents = User::where('role', 'student')->where('status', 'rejected')->count();
        $totalParents   = User::where('role', 'parent')->count();
        $activeTeachers = User::where('role', 'teacher')->where('status', 'approved')->count();
        $totalUsers     = User::count();

        $totalEnrollments = StudentEnrollment::where('status', 'enrolled')->count();

        $registrationsByRole = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->get();

        $enrollmentsByGrade = StudentEnrollment::where('status', 'enrolled')
            ->selectRaw('grade_level, count(*) as total')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        $teacherStats = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->with(['teacherSubjects.subject', 'teacherSubjects.section'])
            ->get();

        return view('admin.reports.index', compact(
            'totalStudents',
            'approvedStudents',
            'pendingStudents',
            'rejectedStudents',
            'totalParents',
            'activeTeachers',
            'totalEnrollments',
            'totalUsers',
            'registrationsByRole',
            'enrollmentsByGrade',
            'teacherStats',
        ));
    }
}