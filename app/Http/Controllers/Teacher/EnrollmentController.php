<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Services\AuditLogService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

/**
 * Enrollment review — ADVISER-driven (DepEd setup, walang HR/Faculty).
 *
 * Ang isang teacher na naka-assign bilang ADVISER ng isang section ang
 * mag-rereview ng mga enrollment request. Dahil ang bagong student ay
 * wala pang section, lahat ng adviser ay nakikita ang "unassigned" pool;
 * kapag inaprubahan, doon ilalagay ang student sa section ng adviser.
 */
class EnrollmentController extends Controller
{
    /** Ang mga section IDs na inaadvise ng kasalukuyang teacher. */
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

        // Pending: ipakita ang mga request na (a) walang section pa (bagong
        // student sa pool) O (b) nasa grade level ng aking advisory section.
        $myGrades = Section::whereIn('id', $mySectionIds)->pluck('grade_level')->unique()->all();

        $enrollments = EnrollmentRequest::with('student')
            ->when($tab === 'pending', function ($q) use ($myGrades) {
                $q->where('status', 'pending');
                // Kung may advisory ang teacher, i-filter sa grade nila;
                // kung wala pa, ipakita lahat ng pending (bagong adviser).
                if (!empty($myGrades)) {
                    $q->whereIn('grade_level', $myGrades);
                }
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
                            ->when(!empty($myGrades), fn($q) => $q->whereIn('grade_level', $myGrades))
                            ->count(),
            'approved' => EnrollmentRequest::where('status', 'approved')->where('reviewed_by', auth()->id())->count(),
            'rejected' => EnrollmentRequest::where('status', 'rejected')->where('reviewed_by', auth()->id())->count(),
        ];

        $hasAdvisory = !empty($mySectionIds);

        return view('teacher.enrollments.index', compact(
            'enrollments', 'tab', 'counts', 'hasAdvisory'
        ));
    }

    // ── Show one request ─────────────────────────────────────────────────────
    public function show(EnrollmentRequest $enrollment)
    {
        $enrollment->load('student');

        // Ang mga section na pwedeng pagpilian — advisory ng teacher, tugma sa grade.
        $mySections = Section::where('adviser_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('teacher.enrollments.show', compact('enrollment', 'mySections'));
    }

    // ── APPROVE — ilagay ang student sa section + email ──────────────────────
    public function approve(Request $request, EnrollmentRequest $enrollment, EnrollmentService $enrollments)
    {
        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        // Seguridad: ang napiling section ay dapat advisory ng teacher na ito.
        $section = Section::where('id', $data['section_id'])
            ->where('adviser_id', auth()->id())
            ->firstOrFail();

        // Pending lang + tugma ang grade — nasa EnrollmentService
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
        // Dati: kahit sinong teacher (kahit hindi adviser) ay kayang mag-reject.
        // Ngayon: adviser lang, at ng grade na hawak niya.
        $myGrades = Section::whereIn('id', $this->myAdvisorySectionIds())->pluck('grade_level')
            ->map(fn ($g) => (string) $g)->all();
        abort_if(empty($myGrades), 403, 'Only section advisers can review enrollments.');

        $requestGrade = (string) $enrollment->grade_level;
        abort_if(
            in_array($requestGrade, ['7', '8', '9', '10', '11', '12'], true) && !in_array($requestGrade, $myGrades, true),
            403,
            'This request is for a grade level you do not advise.'
        );

        $data = $request->validate([
            'remarks' => 'required|string|max:500',
        ], [
            'remarks.required' => 'Please state the reason for rejection.',
        ]);

        $student = $enrollments->reject($enrollment, $data['remarks'], auth()->id());

        AuditLogService::log(
            "Rejected enrollment: {$student->name}",
            'Enrollment (Adviser)',
            "Reason: {$data['remarks']}"
        );

        return redirect()->route('teacher.enrollments.index')
            ->with('success', "Enrollment for {$student->name} was rejected.");
    }
}