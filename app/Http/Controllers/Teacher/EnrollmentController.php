<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
    public function approve(Request $request, EnrollmentRequest $enrollment)
    {
        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        // Seguridad: ang napiling section ay dapat advisory ng teacher na ito.
        $section = Section::where('id', $data['section_id'])
            ->where('adviser_id', auth()->id())
            ->firstOrFail();

        $student = $enrollment->student;

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

        // Email — may section + schedule na. Huwag mag-crash kung pumalya ang SMTP.
        try {
            Mail::send('emails.enrollment-approved', [
                'student'      => $student,
                'request'      => $enrollment,
                'section'      => $section,
                'subjectCount' => $subjectCount,
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Your Enrollment is Approved — DP-LMS');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Enrollment email failed: ' . $e->getMessage());
        }

        AuditLogService::log(
            "Approved enrollment: {$student->name}",
            'Enrollment (Adviser)',
            "Section: {$section->name} | Grade: {$enrollment->grade_level}"
        );

        return redirect()->route('teacher.enrollments.index')
            ->with('success', "{$student->name} is now enrolled in {$section->name}. An email was sent with their details.");
    }

    // ── REJECT ────────────────────────────────────────────────────────────────
    public function reject(Request $request, EnrollmentRequest $enrollment)
    {
        $data = $request->validate([
            'remarks' => 'required|string|max:500',
        ], [
            'remarks.required' => 'Please state the reason for rejection.',
        ]);

        $student = $enrollment->student;

        $enrollment->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks'     => $data['remarks'],
        ]);

        try {
            Mail::send('emails.enrollment-rejected', [
                'student' => $student,
                'request' => $enrollment,
                'reason'  => $data['remarks'],
            ], function ($m) use ($student) {
                $m->to($student->email)->subject('Enrollment Update — DP-LMS');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Enrollment reject email failed: ' . $e->getMessage());
        }

        AuditLogService::log(
            "Rejected enrollment: {$student->name}",
            'Enrollment (Adviser)',
            "Reason: {$data['remarks']}"
        );

        return redirect()->route('teacher.enrollments.index')
            ->with('success', "Enrollment for {$student->name} was rejected. An email was sent.");
    }
}