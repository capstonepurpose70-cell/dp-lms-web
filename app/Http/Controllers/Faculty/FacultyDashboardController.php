<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FacultyDashboardController extends Controller
{
    // ── Auto-share pendingCount sa lahat ng faculty views ──────────────────


    // ── Dashboard ───────────────────────────────────────────────────────────
    public function index()
    {
        $pendingCount   = EnrollmentRequest::where('status', 'pending')->count();
        $approvedToday  = EnrollmentRequest::where('status', 'approved')
            ->whereDate('reviewed_at', today())->count();
        $totalEnrolled  = StudentEnrollment::where('status', 'enrolled')->count();
        $recentRequests = EnrollmentRequest::with('student')
            ->where('status', 'pending')
            ->latest()->take(5)->get();

        return view('faculty.dashboard', compact(
            'pendingCount', 'approvedToday',
            'totalEnrolled', 'recentRequests'
        ));
    }

    // ── Enrollments list ────────────────────────────────────────────────────
    public function enrollments(Request $request)
    {
        $enrollments = EnrollmentRequest::with('student')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->latest()
            ->paginate(15);

        return view('faculty.enrollments', compact('enrollments'));
    }

    // ── Show single enrollment ──────────────────────────────────────────────
    public function showEnrollment(EnrollmentRequest $request)
    {
        $request->load('student');
        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')->get();

        return view('faculty.enrollment-show', compact('request', 'sections'));
    }

    // ── Approve enrollment ──────────────────────────────────────────────────
    public function approveEnrollment(Request $httpRequest, EnrollmentRequest $request)
    {
        $httpRequest->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::find($httpRequest->section_id);
        $student = $request->student;

        $request->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $student->update([
            'section_id'  => $httpRequest->section_id,
            'grade_level' => $request->grade_level,
            'status'      => 'approved',
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'full_name'            => $request->full_name,
                'age'                  => $request->age,
                'birthdate'            => $request->birthdate,
                'gender'               => $request->gender,
                'address'              => $request->address,
                'mother_name'          => $request->mother_name,
                'father_name'          => $request->father_name,
                'guardian_name'        => $request->guardian_name,
                'guardian_contact'     => $request->guardian_contact,
                'student_type'         => $request->student_type,
                'last_school'          => $request->last_school,
                'last_grade_completed' => $request->last_grade_completed,
            ]
        );

        StudentEnrollment::updateOrCreate(
            ['user_id' => $student->id, 'school_year' => $request->school_year],
            [
                'section_id'  => $httpRequest->section_id,
                'grade_level' => $request->grade_level,
                'status'      => 'enrolled',
                'enrolled_at' => now(),
            ]
        );

        $subjectCount = in_array($request->grade_level, ['11', '12']) ? 9 : 8;

        if ($student->contact_number || $student->phone_number) {
            $phone   = $student->phone_number ?? $student->contact_number;
            $message = "Hello {$student->name}! Your enrollment at Sto. Domingo NHS has been approved. "
                     . "Grade: {$request->grade_level} | Section: {$section->name} | "
                     . "Subjects: {$subjectCount} | SY: {$request->school_year}. "
                     . "Log in to DP-LMS to view your dashboard. - DP-LMS";
            SmsService::send($phone, $message);
        }

        try {
            Mail::send('emails.enrollment-approved', [
                'student'      => $student,
                'section'      => $section,
                'request'      => $request,
                'subjectCount' => $subjectCount,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Approved — DP-LMS');
            });
        } catch (\Exception $e) {}

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment',
            "Section: {$section->name} | Grade: {$request->grade_level}"
        );

        return redirect()->route('faculty.enrollments')
            ->with('success', "{$student->name} has been enrolled in {$section->name}.");
    }

    // ── Reject enrollment ───────────────────────────────────────────────────
    public function rejectEnrollment(Request $httpRequest, EnrollmentRequest $request)
    {
        $httpRequest->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $student = $request->student;

        $request->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks'     => $httpRequest->remarks,
        ]);

        try {
            Mail::send('emails.enrollment-rejected', [
                'student' => $student,
                'remarks' => $httpRequest->remarks,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Update — DP-LMS');
            });
        } catch (\Exception $e) {}

        AuditLogService::log(
            "Rejected enrollment: {$student->name}",
            'Enrollment'
        );

        return redirect()->route('faculty.enrollments')
            ->with('success', "Enrollment for {$student->name} has been rejected.");
    }

    // ── Teachers list ───────────────────────────────────────────────────────
    public function teachers(Request $request)
    {
        $teachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->with(['section', 'teacherSubjects'])
            ->when($request->search, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%")
                )
            )
            ->orderBy('name')
            ->paginate(15);

        return view('faculty.teachers', compact('teachers'));
    }

    // ── Show assign form ────────────────────────────────────────────────────
    public function assignTeacher(User $teacher)
    {
        // Make sure this is actually a teacher
        abort_if($teacher->role !== 'teacher', 404);

        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $subjects = Subject::orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $assignedSubjectIds = $teacher->teacherSubjects()
            ->pluck('subject_id')
            ->toArray();

        return view('faculty.teacher-assign', compact(
            'teacher', 'sections', 'subjects', 'assignedSubjectIds'
        ));
    }

    // ── Save assignment ─────────────────────────────────────────────────────
    public function saveAssignment(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);
$request->validate([
    'section_id' => 'nullable|exists:sections,id',
    'subjects'   => 'nullable|array',
    'subjects.*' => 'exists:subjects,id',
]);

// ── Grade level mismatch check ──
if ($request->filled('section_id') && $request->filled('subjects')) {
    $section         = \App\Models\Section::find($request->section_id);
    $mismatchedNames = \App\Models\Subject::whereIn('id', $request->subjects)
        ->where('grade_level', '!=', $section->grade_level)
        ->pluck('name')
        ->toArray();

    if (!empty($mismatchedNames)) {
        return back()->withErrors([
            'subjects' => 'These subjects do not match the section\'s grade level: '
                        . implode(', ', $mismatchedNames),
        ])->withInput();
    }
}
        // Update section
        $teacher->update([
            'section_id' => $request->section_id ?: null,
        ]);

        // Sync subjects — delete old, insert new
        TeacherSubject::where('user_id', $teacher->id)->delete();

        if ($request->filled('subjects')) {
            $section = $request->section_id
                ? \App\Models\Section::find($request->section_id)
                : null;

         $rows = collect($request->subjects)->map(fn($id) => [
    'user_id'     => $teacher->id,
    'subject_id'  => $id,
    'section_id'  => $request->section_id ?: null,
    'grade_level' => $section?->grade_level ?? '',
    'school_year' => \App\Models\SchoolYear::current()?->name ?? '2025-2026',
    'created_at'  => now(),
    'updated_at'  => now(),
])->toArray();

            TeacherSubject::insert($rows);
        }

        $subjectCount = count($request->subjects ?? []);

        AuditLogService::log(
            "Updated teaching assignment: {$teacher->name}",
            'Teacher Assignment',
            "Section ID: {$request->section_id} | Subjects assigned: {$subjectCount}"
        );

        return redirect()->route('faculty.teachers.index')
            ->with('success', "Teaching assignment updated for {$teacher->name}.");
    }
}