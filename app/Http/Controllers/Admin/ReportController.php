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

        // Approved students per grade (pre-computed so the view stays simple/reliable)
        $approvedByGrade = $enrollmentsByGrade->map(fn ($row) =>
            User::where('role', 'student')
                ->where('status', 'approved')
                ->whereHas('studentEnrollment', fn ($q) => $q->where('grade_level', $row->grade_level))
                ->count()
        )->values();

        $teacherStats = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->with(['teacherSubjects.subject', 'teacherSubjects.section'])
            ->get();

        return response()->view('admin.reports.index', compact(
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
            'approvedByGrade',
            'teacherStats',
        ))->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}