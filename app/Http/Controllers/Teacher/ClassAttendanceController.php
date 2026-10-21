<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\ClassAttendanceSession;
use App\Services\ClassAttendanceService;
use Illuminate\Http\Request;

/**
 * Teacher › Attendance — open a class attendance window, show the code, watch it live,
 * correct marks, close early. (Replaces the old page that listed gate-camera scans.)
 */
class ClassAttendanceController extends Controller
{
    public function __construct(private ClassAttendanceService $svc) {}

    public function index()
    {
        $teacher = auth()->user();

        ClassAttendanceSession::whereNull('closed_at')->where('teacher_id', $teacher->id)
            ->where('closes_at', '<=', now())->get()->each(fn ($s) => $this->svc->finalize($s));

        return view('teacher.attendance.index', [
            'classes' => $this->svc->teacherClasses($teacher),
            'open'    => ClassAttendanceSession::with(['section', 'subject'])->open()->where('teacher_id', $teacher->id)->get(),
            'history' => $this->svc->teacherHistory($teacher),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class'           => ['required', 'regex:/^\d+-\d+$/'],   // "{section_id}-{subject_id}"
            'on_time_minutes' => 'required|integer|between:' . ClassAttendanceService::ON_TIME_MIN . ',' . ClassAttendanceService::ON_TIME_MAX,
            'late_minutes'    => 'required|integer|between:0,' . ClassAttendanceService::LATE_MAX,
        ], ['class.required' => 'Please choose a class.']);

        [$sectionId, $subjectId] = array_map('intval', explode('-', $data['class']));

        try {
            $session = $this->svc->open(auth()->user(), $sectionId, $subjectId, (int) $data['on_time_minutes'], (int) $data['late_minutes']);
        } catch (ActionNotAllowed $e) {
            return back()->withInput()->withErrors(['general' => $e->getMessage()]);
        }

        return redirect()->route('teacher.attendance.show', $session)
            ->with('success', 'Attendance is open. Students were notified — show them the code.');
    }

    public function show(ClassAttendanceSession $session)
    {
        $this->guard($session);
        $session->load(['section', 'subject']);

        return view('teacher.attendance.show', ['session' => $session] + $this->svc->sheet($session));
    }

    /** Polled by the live page every few seconds. */
    public function live(ClassAttendanceSession $session)
    {
        $this->guard($session);
        $sheet = $this->svc->sheet($session);
        $session->refresh();

        return response()->json([
            'open'   => $session->isOpen(),
            'code'   => $session->isOpen() ? $session->code : null,
            'counts' => $sheet['counts'],
            'rows'   => $sheet['rows']->map(fn ($r) => [
                'id'        => $r['student']->id,
                'status'    => $r['status'],
                'time'      => $r['record']?->marked_at?->format('g:i A'),
                'by'        => $r['record']?->marked_by,
            ])->values(),
        ]);
    }

    public function code(ClassAttendanceSession $session)
    {
        try {
            $code = $this->svc->newCodeFor(auth()->user(), $session);
        } catch (ActionNotAllowed $e) {
            return $this->fail($e);
        }

        return request()->wantsJson() ? response()->json(['ok' => true, 'code' => $code]) : back()->with('success', 'New code: ' . $code);
    }

    public function close(ClassAttendanceSession $session)
    {
        try {
            $this->svc->close(auth()->user(), $session);
        } catch (ActionNotAllowed $e) {
            return $this->fail($e);
        }

        return back()->with('success', 'Attendance closed. Students who did not check in were marked absent.');
    }

    public function mark(Request $request, ClassAttendanceSession $session)
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'status'     => 'required|in:present,late,absent',
        ]);

        try {
            $record = $this->svc->teacherMark(auth()->user(), $session, (int) $data['student_id'], $data['status']);
        } catch (ActionNotAllowed $e) {
            return $this->fail($e);
        }

        return request()->wantsJson()
            ? response()->json(['ok' => true, 'status' => $record->status, 'time' => $record->marked_at?->format('g:i A')])
            : back()->with('success', 'Attendance updated.');
    }

    private function guard(ClassAttendanceSession $session): void
    {
        abort_unless((int) $session->teacher_id === (int) auth()->id(), 403, 'This attendance belongs to another teacher.');
    }

    private function fail(ActionNotAllowed $e)
    {
        return request()->wantsJson() ? $e->toJson() : back()->withErrors(['general' => $e->getMessage()]);
    }
}
