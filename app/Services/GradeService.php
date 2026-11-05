<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Grade encoding — shared by the web gradebook and the mobile API.
 *
 *  - The grade must belong to a student, and the teacher must teach this
 *    subject in that student's OWN section (not just teach the subject).
 *  - school_year is part of the key, so a new school year never overwrites
 *    last year's grade.
 *  - Percentages belong to one subject of one section in one quarter. They lock
 *    once a grade exists there, so a grade people have seen never changes.
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

        if (!$sectionId || !$this->teaches($teacher, $sectionId, (int) $data['subject_id'])) {
            throw new ActionNotAllowed('You can only grade students in a section where you teach this subject.');
        }

        $ww   = isset($data['written_works']) ? (float) $data['written_works'] : 0.0;
        $pt   = isset($data['performance_tasks']) ? (float) $data['performance_tasks'] : 0.0;
        $qa   = isset($data['quarterly_assessment']) ? (float) $data['quarterly_assessment'] : 0.0;
        $year = self::currentSchoolYear();

        // The class row is held for the save, so percentages cannot change between reading them and the grade existing
        $grade = DB::transaction(function () use ($student, $data, $sectionId, $ww, $pt, $qa, $year) {
            $weights = $this->lockedWeights($sectionId, (int) $data['subject_id'], $data['quarter'], $year)->parts();
            $final   = Grade::computeFinal($ww, $pt, $qa, $weights);

            return Grade::updateOrCreate(
                [
                    'user_id'     => $student->id,
                    'subject_id'  => $data['subject_id'],
                    'quarter'     => $data['quarter'],
                    'school_year' => $year,
                ],
                [
                    'written_works'               => $ww,
                    'performance_tasks'           => $pt,
                    'quarterly_assessment'        => $qa,
                    'final_grade'                 => $final,
                    'weight_written_works'        => $weights['written_works'],
                    'weight_performance_tasks'    => $weights['performance_tasks'],
                    'weight_quarterly_assessment' => $weights['quarterly_assessment'],
                    'remarks'                     => $final >= Grade::PASSING ? 'Passed' : 'Failed',
                ]
            );
        }, 3);

        AuditLogService::log(
            "Updated grade for student {$student->name}",
            'Gradebook',
            "Subject: {$data['subject_id']} | Quarter: {$data['quarter']} | Final: {$grade->final_grade}"
        );

        return $grade;
    }

    public static function weightRules(): array
    {
        return [
            'quarter'              => 'required|in:' . implode(',', self::QUARTERS),
            'written_works'        => 'required|integer|min:0|max:100',
            'performance_tasks'    => 'required|integer|min:0|max:100',
            'quarterly_assessment' => 'required|integer|min:0|max:100',
            'all_quarters'         => 'nullable|boolean',
        ];
    }

    /**
     * Percentages of each of the teacher's classes for this school year, per quarter:
     * ["{section_id}-{subject_id}" => ["Q1" => [written_works, performance_tasks, quarterly_assessment, locked], ...]]
     *
     * @param iterable $classes rows with section_id and subject_id
     */
    public function classWeights(iterable $classes): array
    {
        $classes = collect($classes);
        $year    = self::currentSchoolYear();
        $saved   = GradeWeight::where('school_year', $year)
            ->whereIn('section_id', $classes->pluck('section_id')->unique())
            ->get()
            ->keyBy(fn ($w) => "{$w->section_id}-{$w->subject_id}-{$w->quarter}");

        $map = [];
        foreach ($classes as $class) {
            $key    = "{$class['section_id']}-{$class['subject_id']}";
            $graded = $this->gradesOf($class['section_id'], $class['subject_id'], $year)->distinct()->pluck('quarter')->all();

            foreach (self::QUARTERS as $quarter) {
                $map[$key][$quarter] = ($saved->get("{$key}-{$quarter}")?->parts() ?? Grade::DEFAULT_WEIGHTS)
                    + ['locked' => in_array($quarter, $graded, true)];
            }
        }

        return $map;
    }

    /**
     * Saves percentages for one quarter of a class, or for every quarter that has no grade yet.
     *
     * @param array $data validated with weightRules()
     * @return string[] the quarters saved
     */
    public function setWeights(User $teacher, int $sectionId, int $subjectId, array $data): array
    {
        if (!$this->teaches($teacher, $sectionId, $subjectId)) {
            throw new ActionNotAllowed('You can only set percentages for a class you teach.');
        }

        $weights = array_intersect_key($data, Grade::DEFAULT_WEIGHTS);
        $weights = array_map('intval', $weights);
        $total   = array_sum($weights);
        if ($total !== 100) {
            throw ValidationException::withMessages(['written_works' => "The three percentages must add up to exactly 100%. They add up to {$total}%."]);
        }

        $year     = self::currentSchoolYear();
        $quarters = !empty($data['all_quarters']) ? self::QUARTERS : [$data['quarter']];
        $saved    = [];

        foreach ($quarters as $quarter) {
            $done = DB::transaction(function () use ($sectionId, $subjectId, $quarter, $year, $weights) {
                $row = $this->lockedWeights($sectionId, $subjectId, $quarter, $year);
                if ($this->gradesOf($sectionId, $subjectId, $year)->where('quarter', $quarter)->exists()) {
                    return false;
                }

                return $row->update($weights);
            }, 3);

            if ($done) {
                $saved[] = $quarter;
            }
        }

        if ($saved === []) {
            throw new ActionNotAllowed(!empty($data['all_quarters'])
                ? 'Every quarter of this class already has grades, so its percentages are locked.'
                : "Grades already exist for this class in {$data['quarter']}, so its percentages are locked.", 422);
        }

        AuditLogService::log(
            'Set grade percentages',
            'Gradebook',
            "Section: {$sectionId} | Subject: {$subjectId} | " . implode(', ', $saved) . ' | ' . implode('/', $weights)
        );

        return $saved;
    }

    private function teaches(User $teacher, int $sectionId, int $subjectId): bool
    {
        return TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->exists();
    }

    /** Grades of the students now in the section. */
    private function gradesOf(int $sectionId, int $subjectId, string $year): Builder
    {
        return Grade::where('subject_id', $subjectId)
            ->where('school_year', $year)
            ->whereIn('user_id', User::studentIdsInSections([$sectionId]));
    }

    /** The class's percentages row, created with the default and held until the transaction ends. */
    private function lockedWeights(int $sectionId, int $subjectId, string $quarter, string $year): GradeWeight
    {
        $scope = ['section_id' => $sectionId, 'subject_id' => $subjectId, 'quarter' => $quarter, 'school_year' => $year];
        GradeWeight::firstOrCreate($scope, Grade::DEFAULT_WEIGHTS);

        return GradeWeight::where($scope)->lockForUpdate()->firstOrFail();
    }
}
