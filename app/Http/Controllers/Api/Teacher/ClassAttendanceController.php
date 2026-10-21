<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\ClassAttendanceSession;
use App\Services\ClassAttendanceService;
use Illuminate\Http\Request;

/** Flutter — teacher class attendance (same rules as the web page; logic in ClassAttendanceService). */
class ClassAttendanceController extends Controller
{
    public function __construct(private ClassAttendanceService $svc) {}

    /** GET /api/teacher/class-attendance — classes to choose from, open sessions, recent history. */
    public function index(Request $request)
    {
        $teacher = $request->user();
        ClassAttendanceSession::whereNull('closed_at')->where('teacher_id', $teacher->id)
            ->where('closes_at', '<=', now())->get()->each(fn ($s) => $this->svc->finalize($s));

        return response()->json([
            'classes' => $this->svc->teacherClasses($teacher)->map(fn ($c) => [
                'section_id'   => $c->section_id,
                'section_name' => $c->section->name,
                'grade_level'  => (string) $c->section->grade_level,
                'subject_id'   => $c->subject_id,
                'subject_name' => $c->subject->name,
            ])->values(),
            'open'    => ClassAttendanceSession::with(['section', 'subject'])->open()->where('teacher_id', $teacher->id)
                ->get()->map(fn ($s) => $this->present($s, true))->values(),
            'history' => $this->svc->teacherHistory($teacher)->map(fn ($s) => $this->present($s, false) + [
                'present_count' => (int) $s->present_count,
                'late_count'    => (int) $s->late_count,
                'absent_count'  => (int) $s->absent_count,
            ])->values(),
            'limits'  => ['on_time_max' => ClassAttendanceService::ON_TIME_MAX, 'late_max' => ClassAttendanceService::LATE_MAX],
        ]);
    }

    /** POST /api/teacher/class-attendance { section_id, subject_id, on_time_minutes, late_minutes } */
    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id'      => 'required|integer',
            'subject_id'      => 'required|integer',
            'on_time_minutes' => 'required|integer|between:' . ClassAttendanceService::ON_TIME_MIN . ',' . ClassAttendanceService::ON_TIME_MAX,
            'late_minutes'    => 'required|integer|between:0,' . ClassAttendanceService::LATE_MAX,
        ]);

        try {
            $s = $this->svc->open($request->user(), (int) $data['section_id'], (int) $data['subject_id'],
                (int) $data['on_time_minutes'], (int) $data['late_minutes']);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json(['ok' => true, 'session' => $this->present($s, true)], 201);
    }

    /** GET /api/teacher/class-attendance/{id} — live sheet (poll every ~5 s while open). */
    public function show(Request $request, ClassAttendanceSession $session)
    {
        try {
            $this->svc->assertOwner($request->user(), $session);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }
        $sheet = $this->svc->sheet($session);
        $session->refresh()->load(['section', 'subject']);

        return response()->json([
            'session'  => $this->present($session, true),
            'counts'   => $sheet['counts'],
            'students' => $sheet['rows']->map(fn ($r) => [
                'student_id' => $r['student']->id,
                'name'       => $r['student']->name,
                'lrn'        => $r['student']->lrn,
                'status'     => $r['status'],                                      // null = not yet (still open)
                'marked_at'  => $r['record']?->marked_at?->toIso8601String(),
                'marked_by'  => $r['record']?->marked_by,                          // student | teacher | system
            ])->values(),
        ]);
    }

    /** POST /api/teacher/class-attendance/{id}/code — new code (old one stops working). */
    public function code(Request $request, ClassAttendanceSession $session)
    {
        try {
            return response()->json(['ok' => true, 'code' => $this->svc->newCodeFor($request->user(), $session)]);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }
    }

    /** POST /api/teacher/class-attendance/{id}/close — close now; unmarked → absent. */
    public function close(Request $request, ClassAttendanceSession $session)
    {
        try {
            $this->svc->close($request->user(), $session);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json(['ok' => true, 'counts' => $this->svc->sheet($session)['counts']]);
    }

    /** POST /api/teacher/class-attendance/{id}/mark { student_id, status: present|late|absent } */
    public function mark(Request $request, ClassAttendanceSession $session)
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'status'     => 'required|in:present,late,absent',
        ]);

        try {
            $r = $this->svc->teacherMark($request->user(), $session, (int) $data['student_id'], $data['status']);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json(['ok' => true, 'student_id' => $r->student_id, 'status' => $r->status,
            'marked_at' => $r->marked_at?->toIso8601String()]);
    }

    private function present(ClassAttendanceSession $s, bool $withCode): array
    {
        return [
            'id'              => $s->id,
            'section'         => ['id' => $s->section_id, 'name' => $s->section?->name, 'grade_level' => (string) $s->section?->grade_level],
            'subject'         => ['id' => $s->subject_id, 'name' => $s->subject?->name],
            'code'            => ($withCode && $s->isOpen()) ? $s->code : null,
            'on_time_minutes' => $s->on_time_minutes,
            'late_minutes'    => $s->late_minutes,
            'opened_at'       => $s->opened_at->toIso8601String(),
            'on_time_until'   => $s->on_time_until->toIso8601String(),
            'closes_at'       => $s->closes_at->toIso8601String(),
            'closed_at'       => $s->closed_at?->toIso8601String(),
            'is_open'         => $s->isOpen(),
            'phase'           => !$s->isOpen() ? 'closed' : $s->statusNow(),       // present | late | closed
            'server_time'     => now()->toIso8601String(),
        ];
    }
}
