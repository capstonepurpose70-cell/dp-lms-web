<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Services\AuditLogService;
use App\Services\EnrollmentService;
use App\Services\SmsService;
use Illuminate\Http\Request;

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
    public function approve(Request $httpRequest, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        $httpRequest->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::findOrFail($httpRequest->section_id);

        // reviewed_by = null: ang admin ay nasa hiwalay na `admins` table
        // (dati auth()->id() ng WEB guard — mali o null).
        $student = $enrollments->approve($enrollment, $section, null);
        $grade   = $student->grade_level;

        if ($student->contact_number || $student->phone_number) {
            $phone   = $student->phone_number ?? $student->contact_number;
            $message = "Hello {$student->name}! Your enrollment at Sto. Domingo NHS has been approved. "
                     . "Grade: {$grade} | Section: {$section->name} | "
                     . "Subjects: " . EnrollmentService::subjectCount($grade) . " | SY: {$enrollment->school_year}. "
                     . "Log in to DP-LMS to view your dashboard. - DP-LMS";
            SmsService::send($phone, $message);
        }

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment',
            "Section: {$section->name} | Grade: {$grade}"
        );

        return redirect()->route('admin.enrollment.index')
            ->with('success', "{$student->name} has been enrolled in {$section->name}.");
    }

    // ── Reject an enrollment request ──
    public function reject(Request $httpRequest, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        $httpRequest->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $student = $enrollments->reject($enrollment, $httpRequest->remarks, null);

        AuditLogService::log("Rejected enrollment: {$student->name}", 'Enrollment');

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Enrollment request has been rejected.');
    }
}
