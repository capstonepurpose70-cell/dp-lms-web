<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Services\AuditLogService;
use App\Services\EnrollmentService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->status, ['pending', 'approved', 'rejected'], true) ? $request->status : null;
        $search = trim((string) $request->search);

        $enrollments = EnrollmentRequest::with(['student.section'])
            ->when($status, fn($q) => $q->where('status', $status))
            // Pangalan sa form, pangalan ng account, o LRN
            ->when($search !== '', fn($q) =>
                $q->where(fn($w) =>
                    $w->where('full_name', 'like', "%{$search}%")
                      ->orWhereHas('student', fn($sq) =>
                          $sq->where('name', 'like', "%{$search}%")
                             ->orWhere('lrn', 'like', "%{$search}%"))
                )
            )
            // Pending muna sa itaas — iyon ang kailangang aksyunan
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sections = Section::with('adviser:id,name')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, mb_strtolower($s->name)))
            ->values();

        $counts = EnrollmentRequest::selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('admin.enrollment.index', compact('enrollments', 'sections', 'counts', 'status', 'search'));
    }

    // ── Approve an enrollment request + place the student in a section ──
    public function approve(Request $httpRequest, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        // Active section lang — ang inactive ay hindi na dapat lagyan ng bagong student
        $httpRequest->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')->where('is_active', true)],
        ], ['section_id.exists' => 'Please choose an active section.']);

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

        return back() // keep the admin's filters/page
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

        return back() // keep the admin's filters/page
            ->with('success', 'Enrollment request has been rejected.');
    }
}
