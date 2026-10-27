<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Models\User;
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
            // Name on the form, account name, or LRN
            ->when($search !== '', fn($q) =>
                $q->where(fn($w) =>
                    $w->where('full_name', 'like', "%{$search}%")
                      ->orWhereHas('student', fn($sq) =>
                          $sq->where('name', 'like', "%{$search}%")
                             ->orWhere('lrn', 'like', "%{$search}%"))
                )
            )
            // Pending first — those need action
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

        // Section balancing — stats per grade (one query per grade) + suggestion per pending row
        $balance    = app(\App\Services\SectionBalanceService::class);
        $gradeStats = [];
        $suggested  = [];
        foreach ($enrollments as $e) {
            $g = (string) $e->grade_level;
            if ($e->status !== 'pending' || !in_array($g, ['7', '8', '9', '10', '11', '12'], true)) {
                continue;
            }
            $gradeStats[$g] ??= $balance->forGrade($g);
            $suggested[$e->id] = $balance->suggest($e, $g);
        }
        $sectionSizes = User::where('role', 'student')->where('status', 'approved')->whereNotNull('section_id')
            ->selectRaw('section_id, COUNT(*) as c')->groupBy('section_id')->pluck('c', 'section_id');

        return view('admin.enrollment.index', compact(
            'enrollments', 'sections', 'counts', 'status', 'search', 'gradeStats', 'suggested', 'sectionSizes'
        ));
    }

    // ── Approve an enrollment request + place the student in a section ──
    public function approve(Request $httpRequest, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        // Active sections only — inactive ones should not get new students
        $httpRequest->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')->where('is_active', true)],
        ], ['section_id.exists' => 'Please choose an active section.']);

        $section = Section::findOrFail($httpRequest->section_id);

        // reviewed_by = null: admins live in the separate `admins` table,
        // so the web guard's auth()->id() is not an admin id.
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
