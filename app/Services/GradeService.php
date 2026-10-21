<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pag-encode ng grade — iisa para sa web gradebook at mobile API.
 *
 * Inayos:
 *  - Dati, tsine-check lang kung hawak ng teacher ang SUBJECT → kayang
 *    i-grade ang estudyante ng ibang section (o kahit hindi estudyante).
 *    Ngayon: dapat estudyante, at itinuturo ng teacher ang subject na ito
 *    sa MISMONG section ng estudyante.
 *  - Dati, user+subject+quarter lang ang key → papalitan ng bagong school
 *    year ang grade ng nakaraang taon. Ngayon kasama ang school_year.
 */
class GradeService
{
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
     * Grades na pwedeng makita ng teacher: subject + section PAREHAS na hawak niya.
     * Dati hiwalay ang listahan ng subject at section → nakikita ng Math teacher
     * ng 7-A ang Math grades ng 7-B (na ibang teacher ang nag-encode).
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

    /** @param array $data validated gamit ang rules() */
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
