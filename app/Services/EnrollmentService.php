<?php

namespace App\Services;

use App\Models\EnrollmentRequest;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Enrollment: pag-submit ng estudyante (web + API) at pag-approve/reject
 * (admin + adviser). Dati magkakahiwalay na kopya sa 4 na controller —
 * at magkakaiba ang validation at mga tsek.
 */
class EnrollmentService
{
    private const NAME = 'regex:/^[\pL\s\.\'\-]*$/u';

    public static function rules(): array
    {
        return [
            // grade_level at school_year: SERVER ang nagtatakda, hindi ang estudyante
            'student_type'         => 'required|in:new,old,transfer',
            'full_name'            => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'\-]+$/u'],
            'age'                  => 'required|integer|min:10|max:25',
            'birthdate'            => 'required|date|before:today',
            'gender'               => 'required|in:Male,Female',
            'address'              => 'required|string|max:500',
            'mother_name'          => ['nullable', 'string', 'max:255', self::NAME],
            'father_name'          => ['nullable', 'string', 'max:255', self::NAME],
            'guardian_name'        => ['nullable', 'string', 'max:255', self::NAME],
            'guardian_contact'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
            'last_school'          => 'nullable|string|max:255',
            'last_grade_completed' => 'nullable|string|max:50',
        ];
    }

    public static function messages(): array
    {
        return [
            'full_name.regex'        => 'Full name must contain letters only, no numbers.',
            'mother_name.regex'      => 'Mother\'s name must contain letters only.',
            'father_name.regex'      => 'Father\'s name must contain letters only.',
            'guardian_name.regex'    => 'Guardian\'s name must contain letters only.',
            'guardian_contact.regex' => 'Contact number must contain numbers only.',
        ];
    }

    public static function currentSchoolYear(): string
    {
        return SchoolYear::current()?->label ?? 'Current';
    }

    // ─── Student ──────────────────────────────────────────────────────────────

    /** @param array $data validated gamit ang rules() */
    public function submit(User $student, array $data): EnrollmentRequest
    {
        $schoolYear = static::currentSchoolYear();

        // Dati: form lang ang may tsek — kayang mag-POST nang direkta ang enrolled na.
        $alreadyApproved = EnrollmentRequest::where('user_id', $student->id)
            ->where('status', 'approved')
            ->where('school_year', $schoolYear)
            ->exists();

        if ($alreadyApproved) {
            throw ValidationException::withMessages(['student_type' => 'You are already enrolled for this school year.']);
        }

        $fields = collect($data)->only(array_keys(static::rules()))->all();

        return EnrollmentRequest::updateOrCreate(
            ['user_id' => $student->id, 'status' => 'pending'],
            $fields + [
                'grade_level' => $student->grade_level ?: 'To be assigned',
                'school_year' => $schoolYear,
                'status'      => 'pending',
            ]
        );
    }

    // ─── Reviewer (admin o adviser) ───────────────────────────────────────────

    /**
     * @param int|null $reviewerUserId users.id ng adviser; NULL kapag admin
     *                                 (hiwalay na `admins` table ang admin)
     */
    public function approve(EnrollmentRequest $request, Section $section, ?int $reviewerUserId): User
    {
        $student = $this->assertReviewable($request);

        // Tugma ang grade ng section sa grade ng request (kung alam ang grade).
        $requestGrade = in_array((string) $request->grade_level, ['7', '8', '9', '10', '11', '12'], true)
            ? (string) $request->grade_level
            : null;

        if ($requestGrade !== null && $requestGrade !== (string) $section->grade_level) {
            throw ValidationException::withMessages([
                'section_id' => "The student is Grade {$requestGrade}, but the selected section is Grade {$section->grade_level}.",
            ]);
        }

        $grade = $requestGrade ?? (string) $section->grade_level;

        DB::transaction(function () use ($request, $section, $student, $grade, $reviewerUserId) {
            $request->update([
                'status'      => 'approved',
                'grade_level' => $grade,
                'reviewed_by' => $reviewerUserId,
                'reviewed_at' => now(),
            ]);

            $student->update([
                'section_id'  => $section->id,
                'grade_level' => $grade,
                'status'      => 'approved',
            ]);

            StudentProfile::updateOrCreate(['user_id' => $student->id], [
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
            ]);

            StudentEnrollment::updateOrCreate(
                ['user_id' => $student->id, 'school_year' => $request->school_year],
                [
                    'section_id'  => $section->id,
                    'grade_level' => $grade,
                    'status'      => 'enrolled',
                    'enrolled_at' => now(),
                ]
            );
        });

        $this->mail('emails.enrollment-approved', $student, 'Enrollment Approved — DP-LMS', [
            'student'      => $student,
            'section'      => $section,
            'request'      => $request,
            'subjectCount' => static::subjectCount($grade),
        ]);

        return $student;
    }

    public function reject(EnrollmentRequest $request, ?string $remarks, ?int $reviewerUserId): User
    {
        $student = $this->assertReviewable($request);

        $request->update([
            'status'      => 'rejected',
            'reviewed_by' => $reviewerUserId,
            'reviewed_at' => now(),
            'remarks'     => $remarks,
        ]);

        // Dati: 'reason' ang ipinapasa ng adviser pero `$remarks` ang gamit ng
        // template → error na nilulunok → hindi kailanman naipapadala ang email.
        $this->mail('emails.enrollment-rejected', $student, 'Enrollment Update — DP-LMS', [
            'student' => $student,
            'remarks' => $remarks,
        ]);

        return $student;
    }

    public static function subjectCount(?string $grade): int
    {
        return in_array((string) $grade, ['11', '12'], true) ? 9 : 8;
    }

    /** Pending lang ang puwedeng i-review — hindi na mababago ang tapos na. */
    private function assertReviewable(EnrollmentRequest $request): User
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'general' => "This request was already {$request->status}.",
            ]);
        }

        return $request->student
            ?? throw ValidationException::withMessages(['general' => 'Student record not found for this request.']);
    }

    private function mail(string $view, User $student, string $subject, array $data): void
    {
        if (empty($student->email)) {
            return;
        }

        try {
            Mail::send($view, $data, fn ($m) => $m->to($student->email)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning("Enrollment email failed ({$view}): " . $e->getMessage());
        }
    }
}
