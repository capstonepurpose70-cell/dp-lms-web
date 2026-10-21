<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\ClassAttendanceRecord;
use App\Models\ClassAttendanceSession;
use App\Models\SchoolYear;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Notifications\AttendanceOpened;
use App\Notifications\ClassAttendanceResult;
use App\Notifications\ClassAttendanceResultText;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Class attendance — teacher-posted, student-marked (web + API share this service).
 *
 *  1. Teacher opens a session for a section + subject they teach (teacher_subjects),
 *     choosing the on-time window and the late window. A 4-digit code is generated.
 *  2. Students of that section get a push + in-app notification.
 *  3. Student enters the code shown in class:  within on-time → PRESENT (green),
 *     after it but before closing → LATE (yellow). 5 wrong codes = locked for that session.
 *  4. At closing (or "Close now"), every student without a mark becomes ABSENT.
 *  5. Parents get a push for every result (present / late / absent / teacher correction).
 *
 * Notifications are sent AFTER the HTTP response (defer) and can never break the action.
 * Separate from the Raspberry Pi gate attendance (`attendances` table → ChildArrived).
 */
class ClassAttendanceService
{
    public const ON_TIME_MIN = 1;
    public const ON_TIME_MAX = 120;
    public const LATE_MAX    = 180;
    public const WRONG_CODE_LIMIT = 5;

    public function __construct(private PushNotificationService $push) {}

    // ── Teacher ──────────────────────────────────────────────────────────────

    /** Classes the teacher can take attendance for (from Class Schedules → teacher_subjects). */
    public function teacherClasses(User $teacher): Collection
    {
        return TeacherSubject::with(['section', 'subject'])
            ->where('user_id', $teacher->id)
            ->get()
            ->filter(fn ($ts) => $ts->section && $ts->section->is_active && $ts->subject)
            ->unique(fn ($ts) => $ts->section_id . '-' . $ts->subject_id)
            ->sortBy(fn ($ts) => sprintf('%02d|%s|%s', (int) $ts->section->grade_level, mb_strtolower($ts->section->name), mb_strtolower($ts->subject->name)))
            ->values();
    }

    public function open(User $teacher, int $sectionId, int $subjectId, int $onTimeMinutes, int $lateMinutes): ClassAttendanceSession
    {
        $teaches = TeacherSubject::where('user_id', $teacher->id)
            ->where('section_id', $sectionId)->where('subject_id', $subjectId)->exists();
        if (!$teaches) {
            throw new ActionNotAllowed('You do not teach this subject in this section.', 403);
        }
        if ($onTimeMinutes < self::ON_TIME_MIN || $onTimeMinutes > self::ON_TIME_MAX
            || $lateMinutes < 0 || $lateMinutes > self::LATE_MAX) {
            throw new ActionNotAllowed('Please choose a valid time window.', 422);
        }

        $this->finalizeDueFor($sectionId);
        if (ClassAttendanceSession::open()->where('section_id', $sectionId)->where('subject_id', $subjectId)->exists()) {
            throw new ActionNotAllowed('Attendance for this class is already open.', 422);
        }

        $now = now();
        $session = ClassAttendanceSession::create([
            'teacher_id'      => $teacher->id,
            'section_id'      => $sectionId,
            'subject_id'      => $subjectId,
            'school_year'     => SchoolYear::current()?->label,
            'code'            => self::newCode(),
            'on_time_minutes' => $onTimeMinutes,
            'late_minutes'    => $lateMinutes,
            'opened_at'       => $now,
            'on_time_until'   => $now->copy()->addMinutes($onTimeMinutes),
            'closes_at'       => $now->copy()->addMinutes($onTimeMinutes + $lateMinutes),
        ]);
        $session->load(['subject', 'section', 'teacher']);

        AuditLogService::log('Opened class attendance', 'Attendance',
            "{$session->subject->name} · Grade {$session->section->grade_level} {$session->section->name} · on time {$onTimeMinutes} min, late {$lateMinutes} min");

        $this->later(fn () => $this->notifyStudentsOpened($session));

        return $session;
    }

    public function assertOwner(User $teacher, ClassAttendanceSession $session): void
    {
        if ((int) $session->teacher_id !== (int) $teacher->id) {
            throw new ActionNotAllowed('This attendance belongs to another teacher.', 403);
        }
    }

    public function newCodeFor(User $teacher, ClassAttendanceSession $session): string
    {
        $this->assertOwner($teacher, $session);
        $this->finalizeIfDue($session);
        if (!$session->isOpen()) {
            throw new ActionNotAllowed('This attendance is already closed.', 422);
        }
        $session->update(['code' => self::newCode()]);

        return $session->code;
    }

    public function close(User $teacher, ClassAttendanceSession $session): void
    {
        $this->assertOwner($teacher, $session);
        if ($session->closed_at === null && now()->lt($session->closes_at)) {
            $session->update(['closes_at' => now()]);
        }
        $this->finalize($session);
    }

    /** Teacher marks / corrects one student (also after closing). */
    public function teacherMark(User $teacher, ClassAttendanceSession $session, int $studentId, string $status): ClassAttendanceRecord
    {
        $this->assertOwner($teacher, $session);
        if (!in_array($status, ClassAttendanceRecord::STATUSES, true)) {
            throw new ActionNotAllowed('Invalid status.', 422);
        }
        if (!$this->rosterIds($session)->contains($studentId)) {
            throw new ActionNotAllowed('This student is not in the class.', 422);
        }

        $record  = ClassAttendanceRecord::firstOrNew(['session_id' => $session->id, 'student_id' => $studentId]);
        $existed = $record->exists;
        if ($existed && $record->status === $status) {
            return $record;
        }

        $record->fill([
            'status'    => $status,
            'marked_at' => $status === 'absent' ? null : ($record->marked_at ?? now()),
            'marked_by' => 'teacher',
        ])->save();

        AuditLogService::log("Marked {$status} (class attendance)", 'Attendance', "Session #{$session->id} · student #{$studentId}");

        $record->load(['student.parent', 'session.subject']);
        $this->later(fn () => $this->notifyParents(collect([$record]), $existed));

        return $record;
    }

    /** Roster with each student's current result, for the teacher's live view. */
    public function sheet(ClassAttendanceSession $session): array
    {
        $this->finalizeIfDue($session);
        $records  = $session->records()->get()->keyBy('student_id');
        $students = User::whereIn('id', $this->rosterIds($session))->orderBy('name')->get(['id', 'name', 'lrn']);

        $rows = $students->map(fn ($u) => [
            'student'   => $u,
            'record'    => $records[$u->id] ?? null,
            'status'    => $records[$u->id]->status ?? null,  // null = not marked yet (still open)
        ]);

        return [
            'rows'    => $rows,
            'counts'  => [
                'present' => $rows->where('status', 'present')->count(),
                'late'    => $rows->where('status', 'late')->count(),
                'absent'  => $rows->where('status', 'absent')->count(),
                'waiting' => $rows->whereNull('status')->count(),
                'total'   => $rows->count(),
            ],
        ];
    }

    public function teacherHistory(User $teacher, int $limit = 20): Collection
    {
        return ClassAttendanceSession::with(['section:id,name,grade_level', 'subject:id,name'])
            ->where('teacher_id', $teacher->id)
            ->withCount([
                'records as present_count' => fn ($q) => $q->where('status', 'present'),
                'records as late_count'    => fn ($q) => $q->where('status', 'late'),
                'records as absent_count'  => fn ($q) => $q->where('status', 'absent'),
            ])
            ->latest('opened_at')->take($limit)->get();
    }

    // ── Student ──────────────────────────────────────────────────────────────

    public function mark(User $student, ClassAttendanceSession $session, string $code): ClassAttendanceRecord
    {
        $this->finalizeIfDue($session);
        if (!$this->rosterIds($session)->contains((int) $student->id)) {
            throw new ActionNotAllowed('You are not in this class.', 403);
        }
        $existing = ClassAttendanceRecord::where('session_id', $session->id)->where('student_id', $student->id)->first();
        if (!$session->isOpen()) {
            throw new ActionNotAllowed('Attendance for this class is already closed'
                . ($existing ? " — you were marked {$existing->status}." : '.'), 422);
        }
        if ($existing) {
            throw new ActionNotAllowed("You are already marked {$existing->status} for this class.", 422);
        }

        $key = "class-att:{$session->id}:{$student->id}";
        if (RateLimiter::tooManyAttempts($key, self::WRONG_CODE_LIMIT)) {
            throw new ActionNotAllowed('Too many wrong codes. Please ask your teacher to mark you.', 429);
        }
        $given = preg_replace('/\D/', '', $code);
        if ($given === '' || !hash_equals((string) $session->code, $given)) {
            RateLimiter::hit($key, 3600);
            $left = max(0, self::WRONG_CODE_LIMIT - RateLimiter::attempts($key));
            throw new ActionNotAllowed($left > 0
                ? "Wrong code. {$left} " . ($left === 1 ? 'try' : 'tries') . ' left.'
                : 'Too many wrong codes. Please ask your teacher to mark you.', $left > 0 ? 422 : 429);
        }

        try {
            $record = ClassAttendanceRecord::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'status'     => $session->statusNow(),
                'marked_at'  => now(),
                'marked_by'  => 'student',
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new ActionNotAllowed('You are already marked for this class.', 422);
        }
        RateLimiter::clear($key);

        $record->load(['student.parent', 'session.subject']);
        $this->later(fn () => $this->notifyParents(collect([$record])));

        return $record;
    }

    /** Open sessions for the student's section(s) + their own mark in each. */
    public function studentOpenSessions(User $student): Collection
    {
        $sectionIds = $this->studentSectionIds($student);
        foreach ($sectionIds as $sid) {
            $this->finalizeDueFor($sid);
        }

        return ClassAttendanceSession::with(['subject:id,name', 'section:id,name,grade_level', 'teacher:id,name'])
            ->open()->whereIn('section_id', $sectionIds)->orderBy('closes_at')->get()
            ->filter(fn ($s) => $this->rosterIds($s)->contains((int) $student->id))
            ->map(function ($s) use ($student) {
                $s->setRelation('myRecord', ClassAttendanceRecord::where('session_id', $s->id)->where('student_id', $student->id)->first());
                return $s;
            })->values();
    }

    public function studentHistory(User $student, int $days = 30): Collection
    {
        return ClassAttendanceRecord::with(['session.subject:id,name', 'session.teacher:id,name'])
            ->where('student_id', $student->id)
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->latest('id')->get();
    }

    // ── Finalizing (absents) ─────────────────────────────────────────────────

    public function finalizeIfDue(ClassAttendanceSession $session): void
    {
        if ($session->closed_at === null && now()->gte($session->closes_at)) {
            $this->finalize($session);
        }
    }

    public function finalizeDueFor(int $sectionId): void
    {
        ClassAttendanceSession::whereNull('closed_at')->where('section_id', $sectionId)
            ->where('closes_at', '<=', now())->get()->each(fn ($s) => $this->finalize($s));
    }

    /** Scheduler: every minute (routes/console.php) — also called lazily on every read. */
    public function finalizeAllDue(): int
    {
        $due = ClassAttendanceSession::whereNull('closed_at')->where('closes_at', '<=', now())->get();
        $due->each(fn ($s) => $this->finalize($s));

        return $due->count();
    }

    public function finalize(ClassAttendanceSession $session): void
    {
        $absentIds = DB::transaction(function () use ($session) {
            $locked = ClassAttendanceSession::whereKey($session->id)->lockForUpdate()->first();
            if (!$locked || $locked->closed_at !== null) {
                return [];
            }
            $marked  = ClassAttendanceRecord::where('session_id', $locked->id)->pluck('student_id')->map(fn ($id) => (int) $id);
            $missing = $this->rosterIds($locked)->diff($marked)->values();
            $now     = now();
            if ($missing->isNotEmpty()) {
                ClassAttendanceRecord::insert($missing->map(fn ($id) => [
                    'session_id' => $locked->id, 'student_id' => $id, 'status' => 'absent',
                    'marked_at' => null, 'marked_by' => 'system', 'created_at' => $now, 'updated_at' => $now,
                ])->all());
            }
            $locked->update(['closed_at' => $now]);

            return $missing->all();
        });

        $session->refresh();

        if ($absentIds) {
            $records = ClassAttendanceRecord::with(['student.parent', 'session.subject'])
                ->where('session_id', $session->id)->whereIn('student_id', $absentIds)->get();
            $this->later(fn () => $this->notifyParents($records));
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Approved students of the session's section (same rule as the rest of the app). */
    public function rosterIds(ClassAttendanceSession $session): Collection
    {
        $ids = User::studentIdsInSections([$session->section_id]);

        return User::whereIn('id', $ids)->where('status', 'approved')->pluck('id')->map(fn ($id) => (int) $id)->values();
    }

    private function studentSectionIds(User $student): array
    {
        return collect([$student->studentSectionId(), $student->section_id])->filter()->map(fn ($v) => (int) $v)->unique()->values()->all();
    }

    public static function newCode(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /** After the response on web/API (teacher doesn't wait for 40 pushes); inline in console/scheduler. */
    private function later(callable $fn): void
    {
        $safe = function () use ($fn) {
            try {
                $fn();
            } catch (\Throwable $e) {
                Log::warning('Class attendance notification failed: ' . $e->getMessage());
            }
        };

        app()->runningInConsole() ? $safe() : \Illuminate\Support\defer($safe);
    }

    private function notifyStudentsOpened(ClassAttendanceSession $session): void
    {
        $students = User::whereIn('id', $this->rosterIds($session))->get();
        if ($students->isEmpty()) {
            return;
        }
        Notification::send($students, new AttendanceOpened($session));

        $subject = $session->subject?->name ?? 'class';
        $this->push->sendToUsers(
            $students->pluck('id')->all(),
            "Attendance is open — {$subject}",
            'Enter the class code by ' . $session->on_time_until->format('g:i A') . ' to be present'
                . ($session->late_minutes ? ' (late until ' . $session->closes_at->format('g:i A') . ').' : '.'),
            [
                'type'          => 'attendance_open',
                'session_id'    => $session->id,
                'subject_name'  => $subject,
                'section_name'  => $session->section?->name,
                'on_time_until' => $session->on_time_until->toIso8601String(),
                'closes_at'     => $session->closes_at->toIso8601String(),
            ],
        );
    }

    /** "Every result" to the parent: present / late / absent, and corrections. */
    private function notifyParents(Collection $records, bool $updated = false): void
    {
        foreach ($records as $r) {
            $parent = $r->student?->parent;
            if (!$parent || $parent->status !== 'approved') {
                continue;
            }
            try {
                $parent->notify(new ClassAttendanceResult($r, $updated));
                $this->push->sendToUsers(
                    [$parent->id],
                    ClassAttendanceResultText::title($r, $updated),
                    ClassAttendanceResultText::when($r),
                    [
                        'type'         => 'class_attendance',
                        'student_id'   => $r->student_id,
                        'student_name' => $r->student?->name,
                        'status'       => $r->status,
                        'subject_name' => $r->session?->subject?->name,
                        'session_id'   => $r->session_id,
                        'marked_at'    => $r->marked_at?->toIso8601String() ?? '',
                        'updated'      => $updated ? '1' : '0',
                    ],
                );
            } catch (\Throwable $e) {
                Log::warning('Parent attendance notification failed: ' . $e->getMessage());
            }
        }
    }
}
