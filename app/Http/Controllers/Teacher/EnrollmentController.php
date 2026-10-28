<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Services\AuditLogService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

/**
 * Enrollment review — ADVISER-driven (DepEd setup, no HR/Faculty).
 *
 * A teacher assigned as a section's ADVISER reviews the enrollment requests.
 * New students have no section yet, so every adviser sees the "unassigned"
 * pool; on approval the student is placed in the adviser's section.
 */
class EnrollmentController extends Controller
{
    /** Section ids the current teacher is adviser of. */
    private function myAdvisorySectionIds(): array
    {
        return Section::where('adviser_id', auth()->id())
            ->pluck('id')
            ->all();
    }

    // ── List ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $mySectionIds = $this->myAdvisorySectionIds();
        $tab = $request->query('tab', 'pending'); // pending | approved | rejected

        // Pending: requests that (a) have no section yet (new student in the
        // pool) OR (b) are in the grade level of my advisory section.
        $myGrades = Section::whereIn('id', $mySectionIds)->pluck('grade_level')->unique()->all();

        $enrollments = EnrollmentRequest::with('student')
            ->when($tab === 'pending', function ($q) use ($myGrades) {
                $q->where('status', 'pending');
                // Only advisers review (the principal's rule). No advisory class =
                // no requests listed, since they could not open them anyway.
                $q->whereIn('grade_level', $myGrades ?: ['__none__']);
            })
            ->when($tab === 'approved', fn($q) =>
                $q->where('status', 'approved')->where('reviewed_by', auth()->id()))
            ->when($tab === 'rejected', fn($q) =>
                $q->where('status', 'rejected')->where('reviewed_by', auth()->id()))
            ->when($request->search, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('full_name', 'like', "%{$request->search}%")
                       ->orWhereHas('student', fn($su) =>
                           $su->where('name', 'like', "%{$request->search}%")
                              ->orWhere('lrn', 'like', "%{$request->search}%"))
                ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $counts = [
            'pending'  => EnrollmentRequest::where('status', 'pending')
                            ->whereIn('grade_level', $myGrades ?: ['__none__'])
                            ->count(),
            'approved' => EnrollmentRequest::where('status', 'approved')->where('reviewed_by', auth()->id())->count(),
            'rejected' => EnrollmentRequest::where('status', 'rejected')->where('reviewed_by', auth()->id())->count(),
        ];

        $hasAdvisory = !empty($mySectionIds);

        return view('teacher.enrollments.index', compact(
            'enrollments', 'tab', 'counts', 'hasAdvisory'
        ));
    }

    /**
     * Advisers only, and only for the grade they handle (when the request's grade is known) —
     * a request holds the student's address, parents and birthdate.
     */
    private function authorizeReview(EnrollmentRequest $enrollment): void
    {
        $myGrades = Section::whereIn('id', $this->myAdvisorySectionIds())->pluck('grade_level')
            ->map(fn ($g) => (string) $g)->all();
        abort_if(empty($myGrades), 403, 'Only section advisers can review enrollments.');

        $requestGrade = (string) $enrollment->grade_level;
        abort_if(
            in_array($requestGrade, ['7', '8', '9', '10', '11', '12'], true) && !in_array($requestGrade, $myGrades, true),
            403,
            'This request is for a grade level you do not advise.'
        );
    }

    // ── Show one request ─────────────────────────────────────────────────────
    public function show(EnrollmentRequest $enrollment, \App\Services\SectionBalanceService $balance)
    {
        $this->authorizeReview($enrollment);
        $enrollment->load('student');

        // Sections to choose from — the teacher's advisory sections that match the grade.
        $mySections = Section::where('adviser_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Section balancing: every section of this grade (to show the whole split),
        // and the suggested section — across the grade, and within the teacher's own advisory.
        $grade = in_array((string) $enrollment->grade_level, ['7', '8', '9', '10', '11', '12'], true)
            ? (string) $enrollment->grade_level
            : (string) ($mySections->first()->grade_level ?? '');
        $balanceStats  = $grade !== '' ? $balance->forGrade($grade) : collect();
        $bestOverall   = $grade !== '' ? $balance->suggest($enrollment, $grade) : null;
        $bestMine      = $grade !== '' ? $balance->suggest($enrollment, $grade, $mySections->pluck('id')->all()) : null;

        return view('teacher.enrollments.show', compact('enrollment', 'mySections', 'balanceStats', 'bestOverall', 'bestMine', 'grade'));
    }

    // ── APPROVE — place the student in a section + email ─────────────────────
    public function approve(Request $request, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        // Security: the chosen section must be this teacher's advisory section.
        $section = Section::where('id', $data['section_id'])
            ->where('adviser_id', auth()->id())
            ->firstOrFail();

        // Pending only + matching grade — checked in EnrollmentService
        $student = $enrollments->approve($enrollment, $section, auth()->id());

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment (Adviser)',
            "Section: {$section->name} | Grade: {$section->grade_level}"
        );

        return redirect()->route('teacher.enrollments.index')
            ->with('success', "{$student->name} is now enrolled in {$section->name}.");
    }

    // ── REJECT ────────────────────────────────────────────────────────────────
    public function reject(Request $request, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        // Only the adviser can reject, and only for the grade they handle
        // (not any teacher who knows the URL).
        $this->authorizeReview($enrollment);

        $data = $request->validate([
            'remarks' => 'required|string|max:500',
        ], [
            'remarks.required' => 'Tell the student what needs to change before returning the form.',
            'remarks.max'      => 'Keep the message under 500 characters.',
        ]);

        $student = $enrollments->reject($enrollment, $data['remarks'], auth()->id());

        AuditLogService::log(
            "Rejected enrollment: {$student->name}",
            'Enrollment (Adviser)',
            "Reason: {$data['remarks']}"
        );

        return redirect()->route('teacher.enrollments.index')
            ->with('success', "The form of {$student->name} was returned.");
    }
}