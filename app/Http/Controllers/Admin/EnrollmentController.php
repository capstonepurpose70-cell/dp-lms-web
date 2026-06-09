<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Services\AuditLogService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = EnrollmentRequest::with(['student.section'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('name', 'like', "%{$request->search}%")
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sections = Section::where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('admin.enrollment.index', compact('enrollments', 'sections'));
    }

    // ── Approve an enrollment request + place the student in a section ──
    public function approve(Request $httpRequest, EnrollmentRequest $enrollment)
    {
        $httpRequest->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::find($httpRequest->section_id);
        $student = $enrollment->student;

        if (!$student) {
            return back()->with('error', 'Student record not found for this request.');
        }

        $enrollment->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $student->update([
            'section_id'  => $section->id,
            'grade_level' => $enrollment->grade_level,
            'status'      => 'approved',
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'full_name'            => $enrollment->full_name,
                'age'                  => $enrollment->age,
                'birthdate'            => $enrollment->birthdate,
                'gender'               => $enrollment->gender,
                'address'              => $enrollment->address,
                'mother_name'          => $enrollment->mother_name,
                'father_name'          => $enrollment->father_name,
                'guardian_name'        => $enrollment->guardian_name,
                'guardian_contact'     => $enrollment->guardian_contact,
                'student_type'         => $enrollment->student_type,
                'last_school'          => $enrollment->last_school,
                'last_grade_completed' => $enrollment->last_grade_completed,
            ]
        );

        StudentEnrollment::updateOrCreate(
            ['user_id' => $student->id, 'school_year' => $enrollment->school_year],
            [
                'section_id'  => $section->id,
                'grade_level' => $enrollment->grade_level,
                'status'      => 'enrolled',
                'enrolled_at' => now(),
            ]
        );

        $subjectCount = in_array($enrollment->grade_level, ['11', '12']) ? 9 : 8;

        if ($student->contact_number || $student->phone_number) {
            $phone   = $student->phone_number ?? $student->contact_number;
            $message = "Hello {$student->name}! Your enrollment at Sto. Domingo NHS has been approved. "
                     . "Grade: {$enrollment->grade_level} | Section: {$section->name} | "
                     . "Subjects: {$subjectCount} | SY: {$enrollment->school_year}. "
                     . "Log in to DP-LMS to view your dashboard. - DP-LMS";
            SmsService::send($phone, $message);
        }

        try {
            Mail::send('emails.enrollment-approved', [
                'student'      => $student,
                'section'      => $section,
                'request'      => $enrollment,
                'subjectCount' => $subjectCount,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Approved — DP-LMS');
            });
        } catch (\Exception $e) {}

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment',
            "Section: {$section->name} | Grade: {$enrollment->grade_level}"
        );

        return redirect()->route('admin.enrollment.index')
            ->with('success', "{$student->name} has been enrolled in {$section->name}.");
    }

    // ── Reject an enrollment request ──
    public function reject(Request $httpRequest, EnrollmentRequest $enrollment)
    {
        $httpRequest->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $student = $enrollment->student;

        $enrollment->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks'     => $httpRequest->remarks,
        ]);

        if ($student) {
            try {
                Mail::send('emails.enrollment-rejected', [
                    'student' => $student,
                    'remarks' => $httpRequest->remarks,
                ], function ($m) use ($student) {
                    $m->to($student->email)->subject('Enrollment Update — DP-LMS');
                });
            } catch (\Exception $e) {}

            AuditLogService::log("Rejected enrollment: {$student->name}", 'Enrollment');
        }

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Enrollment request has been rejected.');
    }
}