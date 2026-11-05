<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\ClassSchedule;
use App\Models\MeetLink;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Google Meet links, shared by the website and the mobile API.
 *
 *  - One link per subject and section. The teacher assigned to that class manages it, so a
 *    replacement teacher takes it over with the class.
 *  - Students see a link only for a subject taught in their own section this term.
 */
class MeetLinkService
{
    public function __construct(
        private ClassAttendanceService $classes,
        private StudentNotifier $notifier,
    ) {}

    /** The address as stored, or null when it is not a Google Meet link. Pasted extras are dropped. */
    public static function normalize(string $input): ?string
    {
        $pattern = '~^(?:(?:https://)?meet\.google\.com/)?([a-z]{3}-[a-z]{4}-[a-z]{3}|lookup/[a-z0-9]{5,40})(?:\?[^\s<>"\']*)?$~';

        return preg_match($pattern, strtolower(trim($input)), $m) ? 'https://meet.google.com/' . $m[1] : null;
    }

    /** Every class of the teacher, with its link or null. */
    public function forTeacher(User $teacher): Collection
    {
        $links = MeetLink::where('school_year', GradeService::currentSchoolYear())
            ->whereIn('section_id', TeacherSubject::where('user_id', $teacher->id)->pluck('section_id'))
            ->get()
            ->keyBy(fn ($l) => $l->section_id . '-' . $l->subject_id);

        return $this->classes->teacherClasses($teacher)->map(fn ($ts) => [
            'class'       => $ts->section_id . '-' . $ts->subject_id,
            'section_id'  => (int) $ts->section_id,
            'subject_id'  => (int) $ts->subject_id,
            'subject'     => $ts->subject->name,
            'section'     => $ts->section->name,
            'grade_level' => $ts->section->grade_level,
            'url'         => $links->get($ts->section_id . '-' . $ts->subject_id)?->url,
        ])->values();
    }

    /** Links of the student's own section. */
    public function forStudent(User $student): Collection
    {
        $sectionId = $student->studentSectionId();
        if (!$sectionId) {
            return collect();
        }

        $links = MeetLink::where('section_id', $sectionId)
            ->where('school_year', GradeService::currentSchoolYear())
            ->get();
        if ($links->isEmpty()) {
            return collect();
        }

        $teaching = TeacherSubject::with(['teacher:id,name', 'subject:id,name'])
            ->where('section_id', $sectionId)
            ->whereIn('subject_id', $links->pluck('subject_id'))
            ->get()
            ->keyBy('subject_id');

        // Senior High: a subject scheduled only in the other semester is not running now
        $scheduled = ClassSchedule::where('section_id', $sectionId)->pluck('subject_id');
        $thisTerm  = ClassSchedule::where('section_id', $sectionId)->forTerm(SchoolYear::currentSemester())->pluck('subject_id');

        return $links
            ->filter(fn ($l) => $teaching->has($l->subject_id)
                && (!$scheduled->contains($l->subject_id) || $thisTerm->contains($l->subject_id)))
            ->map(fn ($l) => [
                'id'         => $l->id,
                'subject'    => $teaching[$l->subject_id]->subject?->name ?? 'Subject',
                'teacher'    => $teaching[$l->subject_id]->teacher?->name,
                'url'        => $l->url,
                'updated_at' => $l->updated_at?->toIso8601String(),
            ])
            ->sortBy(fn ($row) => mb_strtolower($row['subject']))
            ->values();
    }

    public function publish(User $teacher, int $sectionId, int $subjectId, string $input): MeetLink
    {
        $this->assertTeaches($teacher, $sectionId, $subjectId);

        $url = self::normalize($input);
        if ($url === null) {
            throw ValidationException::withMessages(['url' => 'Paste a Google Meet link, like https://meet.google.com/abc-defg-hij.']);
        }

        $link = MeetLink::firstOrNew([
            'section_id'  => $sectionId,
            'subject_id'  => $subjectId,
            'school_year' => GradeService::currentSchoolYear(),
        ]);
        $changed = $link->url !== $url;

        $link->fill(['url' => $url, 'updated_by' => $teacher->id])->save();

        if ($changed) {
            AuditLogService::log('Published a Google Meet link', 'Google Meet', "Section: {$sectionId} | Subject: {$subjectId}");
            $this->notifier->meetLink($link, $teacher);
        }

        return $link;
    }

    public function remove(User $teacher, int $sectionId, int $subjectId): void
    {
        $this->assertTeaches($teacher, $sectionId, $subjectId);

        $removed = MeetLink::where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->where('school_year', GradeService::currentSchoolYear())
            ->delete();

        if ($removed) {
            AuditLogService::log('Removed a Google Meet link', 'Google Meet', "Section: {$sectionId} | Subject: {$subjectId}");
        }
    }

    private function assertTeaches(User $teacher, int $sectionId, int $subjectId): void
    {
        $teaches = TeacherSubject::where('user_id', $teacher->id)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$teaches) {
            throw new ActionNotAllowed('You can only manage the Google Meet link of a class you teach.');
        }
    }
}
