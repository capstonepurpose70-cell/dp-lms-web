<?php

namespace App\Http\Controllers\Api\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\ClassAttendanceSession;
use App\Services\ClassAttendanceService;
use Illuminate\Http\Request;

/** Flutter — student class attendance (enter the code shown in class). */
class ClassAttendanceController extends Controller
{
    public function __construct(private ClassAttendanceService $svc) {}

    /** GET /api/student/class-attendance — open sessions (with my mark) + last 30 days. */
    public function index(Request $request)
    {
        $student = $request->user();

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'open'        => $this->svc->studentOpenSessions($student)->map(fn ($s) => [
                'session_id'    => $s->id,
                'subject_name'  => $s->subject?->name,
                'section_name'  => $s->section?->name,
                'teacher_name'  => $s->teacher?->name,
                'on_time_until' => $s->on_time_until->toIso8601String(),
                'closes_at'     => $s->closes_at->toIso8601String(),
                'phase'         => $s->statusNow(),                               // present | late
                'my_status'     => $s->myRecord?->status,                         // null = not marked yet
                'my_marked_at'  => $s->myRecord?->marked_at?->toIso8601String(),
            ])->values(),
            'history'     => $this->svc->studentHistory($student)->map(fn ($r) => [
                'session_id'   => $r->session_id,
                'subject_name' => $r->session?->subject?->name,
                'teacher_name' => $r->session?->teacher?->name,
                'date'         => $r->session?->opened_at?->toDateString(),
                'status'       => $r->status,
                'marked_at'    => $r->marked_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    /** POST /api/student/class-attendance/{id} { code } */
    public function mark(Request $request, ClassAttendanceSession $session)
    {
        $data = $request->validate(['code' => 'required|string|max:10']);

        try {
            $r = $this->svc->mark($request->user(), $session, $data['code']);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'        => true,
            'status'    => $r->status,                       // present (green) | late (yellow)
            'marked_at' => $r->marked_at->toIso8601String(),
            'message'   => 'You are marked ' . strtoupper($r->status) . ' at ' . $r->marked_at->format('g:i A') . '.',
        ]);
    }
}
