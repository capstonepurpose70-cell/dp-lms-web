<?php

namespace App\Http\Controllers\Api\Faculty;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\SchoolYear;
use App\Services\AuditLogService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    // ── GET /api/faculty/dashboard ────────────────────────────────────────────
    public function index()
    {
        $pendingCount  = EnrollmentRequest::where('status', 'pending')->count();
        $approvedToday = EnrollmentRequest::where('status', 'approved')
            ->whereDate('reviewed_at', today())->count();
        $totalEnrolled = StudentEnrollment::where('status', 'enrolled')->count();

        $recentRequests = EnrollmentRequest::with('student')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'student_name'=> $r->student?->name,
                'grade_level' => $r->grade_level,
                'student_type'=> $r->student_type,
                'school_year' => $r->school_year,
                'submitted_at'=> $r->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'summary' => [
                'pending_count'  => $pendingCount,
                'approved_today' => $approvedToday,
                'total_enrolled' => $totalEnrolled,
            ],
            'recent_requests' => $recentRequests,
        ]);
    }

    // ── GET /api/faculty/enrollments ──────────────────────────────────────────
    // Optional query params: status (pending|approved|rejected), search, page
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

        return response()->json([
            'data' => $enrollments->map(fn($r) => [
                'id'           => $r->id,
                'student_name' => $r->student?->name,
                'student_email'=> $r->student?->email,
                'student_lrn'  => $r->student?->lrn,
                'full_name'    => $r->full_name,
                'grade_level'  => $r->grade_level,
                'school_year'  => $r->school_year,
                'student_type' => $r->student_type,
                'gender'       => $r->gender,
                'age'          => $r->age,
                'birthdate'    => $r->birthdate?->toDateString(),
                'address'      => $r->address,
                'mother_name'  => $r->mother_name,
                'father_name'  => $r->father_name,
                'guardian_name'=> $r->guardian_name,
                'guardian_contact' => $r->guardian_contact,
                'last_school'  => $r->last_school,
                'last_grade_completed' => $r->last_grade_completed,
                'status'       => $r->status,
                'remarks'      => $r->remarks,
                'reviewed_at'  => $r->reviewed_at?->toDateTimeString(),
                'submitted_at' => $r->created_at?->toDateTimeString(),
                'time_ago'     => $r->created_at?->diffForHumans(),
            ]),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'last_page'    => $enrollments->lastPage(),
                'per_page'     => $enrollments->perPage(),
                'total'        => $enrollments->total(),
            ],
        ]);
    }

    // ── GET /api/faculty/enrollments/{id} ─────────────────────────────────────
    public function showEnrollment(int $id)
    {
        $enrollmentRequest = EnrollmentRequest::with('student')->findOrFail($id);

        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'grade_level' => $s->grade_level,
            ]);

        return response()->json([
            'enrollment' => [
                'id'           => $enrollmentRequest->id,
                'student_name' => $enrollmentRequest->student?->name,
                'student_email'=> $enrollmentRequest->student?->email,
                'student_lrn'  => $enrollmentRequest->student?->lrn,
                'full_name'    => $enrollmentRequest->full_name,
                'grade_level'  => $enrollmentRequest->grade_level,
                'school_year'  => $enrollmentRequest->school_year,
                'student_type' => $enrollmentRequest->student_type,
                'gender'       => $enrollmentRequest->gender,
                'age'          => $enrollmentRequest->age,
                'birthdate'    => $enrollmentRequest->birthdate?->toDateString(),
                'address'      => $enrollmentRequest->address,
                'mother_name'  => $enrollmentRequest->mother_name,
                'father_name'  => $enrollmentRequest->father_name,
                'guardian_name'=> $enrollmentRequest->guardian_name,
                'guardian_contact' => $enrollmentRequest->guardian_contact,
                'last_school'  => $enrollmentRequest->last_school,
                'last_grade_completed' => $enrollmentRequest->last_grade_completed,
                'status'       => $enrollmentRequest->status,
                'submitted_at' => $enrollmentRequest->created_at?->toDateTimeString(),
            ],
            'available_sections' => $sections,
        ]);
    }

    // ── POST /api/faculty/enrollments/{id}/approve ────────────────────────────
    // Body: { section_id }
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $enrollmentRequest = EnrollmentRequest::with('student')->findOrFail($id);

        if ($enrollmentRequest->status !== 'pending') {
            return response()->json([
                'message' => "This enrollment is already {$enrollmentRequest->status}.",
            ], 422);
        }

        $section = Section::findOrFail($request->section_id);
        $student = $enrollmentRequest->student;

        // Update enrollment request
        $enrollmentRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Update student record
        $student->update([
            'section_id'  => $section->id,
            'grade_level' => $enrollmentRequest->grade_level,
            'status'      => 'approved',
        ]);

        // Save student profile
        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'full_name'            => $enrollmentRequest->full_name,
                'age'                  => $enrollmentRequest->age,
                'birthdate'            => $enrollmentRequest->birthdate,
                'gender'               => $enrollmentRequest->gender,
                'address'              => $enrollmentRequest->address,
                'mother_name'          => $enrollmentRequest->mother_name,
                'father_name'          => $enrollmentRequest->father_name,
                'guardian_name'        => $enrollmentRequest->guardian_name,
                'guardian_contact'     => $enrollmentRequest->guardian_contact,
                'student_type'         => $enrollmentRequest->student_type,
                'last_school'          => $enrollmentRequest->last_school,
                'last_grade_completed' => $enrollmentRequest->last_grade_completed,
            ]
        );

        // Create student enrollment record
        StudentEnrollment::updateOrCreate(
            ['user_id' => $student->id, 'school_year' => $enrollmentRequest->school_year],
            [
                'section_id'  => $section->id,
                'grade_level' => $enrollmentRequest->grade_level,
                'status'      => 'enrolled',
                'enrolled_at' => now(),
            ]
        );

        $subjectCount = in_array($enrollmentRequest->grade_level, ['11', '12']) ? 9 : 8;

        // Send SMS notification
        $phone = $student->phone_number ?? $student->contact_number;
        if ($phone) {
            SmsService::send($phone,
                "Hello {$student->name}! Your enrollment has been approved. " .
                "Grade: {$enrollmentRequest->grade_level} | Section: {$section->name} | " .
                "Subjects: {$subjectCount} | SY: {$enrollmentRequest->school_year}. " .
                "Log in to DP-LMS to view your dashboard. - DP-LMS"
            );
        }

        // Send email notification
        try {
            Mail::send('emails.enrollment-approved', [
                'student'      => $student,
                'section'      => $section,
                'request'      => $enrollmentRequest,
                'subjectCount' => $subjectCount,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Approved — DP-LMS');
            });
        } catch (\Exception $e) {
            // Email failed — non-fatal, continue
        }

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment',
            "Section: {$section->name} | Grade: {$enrollmentRequest->grade_level}"
        );

        return response()->json([
            'message' => "{$student->name} has been enrolled in {$section->name}.",
            'student' => [
                'id'          => $student->id,
                'name'        => $student->name,
                'grade_level' => $enrollmentRequest->grade_level,
                'section'     => $section->name,
                'status'      => 'approved',
            ],
        ]);
    }

    // ── POST /api/faculty/enrollments/{id}/reject ─────────────────────────────
    // Body: { remarks? }
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $enrollmentRequest = EnrollmentRequest::with('student')->findOrFail($id);

        if ($enrollmentRequest->status !== 'pending') {
            return response()->json([
                'message' => "This enrollment is already {$enrollmentRequest->status}.",
            ], 422);
        }

        $student = $enrollmentRequest->student;

        $enrollmentRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks'     => $request->remarks,
        ]);

        // Send email notification
        try {
            Mail::send('emails.enrollment-rejected', [
                'student' => $student,
                'remarks' => $request->remarks,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Update — DP-LMS');
            });
        } catch (\Exception $e) {
            // Email failed — non-fatal, continue
        }

        AuditLogService::log(
            "Rejected enrollment: {$student->name}",
            'Enrollment'
        );

        return response()->json([
            'message' => "Enrollment for {$student->name} has been rejected.",
        ]);
    }

    // ── GET /api/faculty/teachers ─────────────────────────────────────────────
    // Optional query params: search
    public function teachers(Request $request)
    {
        $teachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->with(['section', 'teacherSubjects.subject'])
            ->when($request->search, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%")
                )
            )
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'data' => $teachers->map(fn($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'email'       => $t->email,
                'employee_id' => $t->employee_id,
                'section'     => $t->section?->name,
                'section_id'  => $t->section_id,
                'subjects'    => $t->teacherSubjects->map(fn($ts) => [
                    'subject_id'   => $ts->subject?->id,
                    'subject_name' => $ts->subject?->name,
                ]),
            ]),
            'pagination' => [
                'current_page' => $teachers->currentPage(),
                'last_page'    => $teachers->lastPage(),
                'total'        => $teachers->total(),
            ],
        ]);
    }

    // ── GET /api/faculty/teachers/{id}/assign ─────────────────────────────────
    // Returns form data needed to assign teacher
    public function showAssignTeacher(int $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'grade_level' => $s->grade_level,
            ]);

        $subjects = Subject::orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->map(fn($sub) => [
                'id'          => $sub->id,
                'name'        => $sub->name,
                'code'        => $sub->code,
                'grade_level' => $sub->grade_level,
            ]);

        $assignedSubjectIds = $teacher->teacherSubjects()
            ->pluck('subject_id')
            ->toArray();

        return response()->json([
            'teacher' => [
                'id'          => $teacher->id,
                'name'        => $teacher->name,
                'section_id'  => $teacher->section_id,
            ],
            'sections'             => $sections,
            'subjects'             => $subjects,
            'assigned_subject_ids' => $assignedSubjectIds,
        ]);
    }

    // ── POST /api/faculty/teachers/{id}/assign ────────────────────────────────
    // Body: { section_id?, subjects: [1,2,3] }
    public function saveAssignment(Request $request, int $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'subjects'   => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        // Grade level mismatch check
        if ($request->filled('section_id') && $request->filled('subjects')) {
            $section         = Section::find($request->section_id);
            $mismatchedNames = Subject::whereIn('id', $request->subjects)
                ->where('grade_level', '!=', $section->grade_level)
                ->pluck('name')
                ->toArray();

            if (!empty($mismatchedNames)) {
                return response()->json([
                    'message' => 'These subjects do not match the section\'s grade level: '
                               . implode(', ', $mismatchedNames),
                ], 422);
            }
        }

        // Update section
        $teacher->update([
            'section_id' => $request->section_id ?: null,
        ]);

        // Sync subjects
        TeacherSubject::where('user_id', $teacher->id)->delete();

        if ($request->filled('subjects')) {
            $section = $request->section_id
                ? Section::find($request->section_id)
                : null;

            $rows = collect($request->subjects)->map(fn($subId) => [
                'user_id'     => $teacher->id,
                'subject_id'  => $subId,
                'section_id'  => $request->section_id ?: null,
                'grade_level' => $section?->grade_level ?? '',
                'school_year' => SchoolYear::current()?->name ?? now()->year . '-' . (now()->year + 1),
                'created_at'  => now(),
                'updated_at'  => now(),
            ])->toArray();

            TeacherSubject::insert($rows);
        }

        AuditLogService::log(
            "Updated teaching assignment: {$teacher->name}",
            'Teacher Assignment',
            "Section ID: {$request->section_id} | Subjects: " . count($request->subjects ?? [])
        );

        return response()->json([
            'message' => "Teaching assignment updated for {$teacher->name}.",
        ]);
    }
}
