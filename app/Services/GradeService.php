<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Grade encoding — shared by the web gradebook and the mobile API.
 *
 *  - The grade must belong to a student, and the teacher must teach this
 *    subject in that student's OWN section (not just teach the subject).
 *  - school_year is part of the key, so a new school year never overwrites
 *    last year's grade.
 */
class GradeService
{
    public const QUARTERS = ['Q1', 'Q2', 'Q3', 'Q4'];

    public const UNSPECIFIED_YEAR = 'Unspecified';

    public static function rules(): array
    {
        return [
            'student_id'           => 'required|integer',
            'subject_id'           => 'required|exists:subjects,id',
            'quarter'              => 'required|in:Q1,Q2,Q3,Q4',
            'written_works'        => 'nullable|numeric|min:0|max:100',
            'performance_tasks'    => 'nullable|numeric|min:0|max:100',
            'quarterly_assessment' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /** Readable field names for validation messages */
    public static function attributes(): array
    {
        return [
            'student_id'           => 'student',
            'subject_id'           => 'class',
            'quarter'              => 'quarter',
            'written_works'        => 'written works',
            'performance_tasks'    => 'performance tasks',
            'quarterly_assessment' => 'quarterly assessment',
        ];
    }

    public static function currentSchoolYear(): string
    {
        return SchoolYear::current()?->label ?? now()->year . '-' . (now()->year + 1);
    }

    /**
     * Grades the teacher may see: subject AND section both handled by them
     * (the Math teacher of 7-A must not see 7-B's Math grades).
     */
    public function visibleTo(User $teacher): Builder
    {
        $pairs = TeacherSubject::where('user_id', $teacher->id)->get(['subject_id', 'section_id'])
            ->groupBy('subject_id');

        return Grade::query()->where(function (Builder $q) use ($pairs) {
            if ($pairs->isEmpty()) {
                $q->whereRaw('1 = 0');
                return;
            }
            foreach ($pairs as $subjectId => $rows) {
                $studentIds = User::studentIdsInSections($rows->pluck('section_id'));
                $q->orWhere(fn (Builder $w) => $w->where('subject_id', $subjectId)->whereIn('user_id', $studentIds));
            }
        });
    }

    /**
     * Report card for one student's grades (subject relation loaded), newest school year first:
     *
     *   [school_year => ['subjects' => [...], 'general' => ?float, 'complete' => bool, 'current' => bool]]
     *
     * A subject's average is the mean of its recorded quarters; the general average is the mean
     * of the subject averages. Used by the student Grades page and the parent web/API so both
     * always show the same numbers.
     */
    public static function reportCard(iterable $grades): Collection
    {
        $current = self::currentSchoolYear();

        $years = collect($grades)
            ->groupBy(fn (Grade $g) => $g->school_year ?: self::UNSPECIFIED_YEAR)
            ->sortKeysDesc()
            ->map(function (Collection $yearGrades, string $year) use ($current) {
                $subjects = $yearGrades->groupBy('subject_id')->map(function (Collection $rows) {
                    $byQuarter = $rows->keyBy('quarter');
                    $finals    = $rows->pluck('final_grade')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                    $average   = $finals->isNotEmpty() ? round($finals->avg(), 2) : null;

                    return [
                        'subject'  => $rows->first()->subject,
                        'quarters' => collect(self::QUARTERS)->mapWithKeys(fn ($q) => [$q => $byQuarter->get($q)]),
                        'average'  => $average,
                        'complete' => $finals->count() === count(self::QUARTERS),
                        'passed'   => $average !== null && $average >= Grade::PASSING,
                    ];
                })->sortBy(fn ($s) => mb_strtolower($s['subject']?->name ?? ''))->values();

                $averages = $subjects->pluck('average')->filter(fn ($v) => $v !== null);

                return [
                    'subjects' => $subjects,
                    'general'  => $averages->isNotEmpty() ? round($averages->avg(), 2) : null,
                    'complete' => $subjects->isNotEmpty() && $subjects->every(fn ($s) => $s['complete']),
                    'current'  => $year === $current,   // a past year with missing quarters is incomplete, not in progress
                ];
            });

        // Rows saved before school_year existed go last, not first ("U" sorts above digits)
        return $years->except(self::UNSPECIFIED_YEAR)->union($years->only(self::UNSPECIFIED_YEAR));
    }

    /** @param array $data validated with rules() */
    public function save(User $teacher, array $data): Grade
    {
        $student = User::where('role', 'student')->find($data['student_id']);
        if (!$student) {
            throw new ActionNotAllowed('The selected account is not a student.', 422);
        }

        $sectionId = $student->studentSectionId();
        $teaches   = $sectionId && TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $data['subject_id'])
            ->where('section_id', $sectionId)
            ->exists();

        if (!$teaches) {
            throw new ActionNotAllowed('You can only grade students in a section where you teach this subject.');
        }

        $ww    = isset($data['written_works']) ? (float) $data['written_works'] : 0.0;
        $pt    = isset($data['performance_tasks']) ? (float) $data['performance_tasks'] : 0.0;
        $qa    = isset($data['quarterly_assessment']) ? (float) $data['quarterly_assessment'] : 0.0;
        $final = Grade::computeFinal($ww, $pt, $qa);

        $grade = Grade::updateOrCreate(
            [
                'user_id'     => $student->id,
                'subject_id'  => $data['subject_id'],
                'quarter'     => $data['quarter'],
                'school_year' => self::currentSchoolYear(),
            ],
            [
                'written_works'        => $ww,
                'performance_tasks'    => $pt,
                'quarterly_assessment' => $qa,
                'final_grade'          => $final,
                'remarks'              => $final >= Grade::PASSING ? 'Passed' : 'Failed',
            ]
        );

        AuditLogService::log(
            "Updated grade for student {$student->name}",
            'Gradebook',
            "Subject: {$data['subject_id']} | Quarter: {$data['quarter']} | Final: {$final}"
        );

        return $grade;
    }
}
