<?php

namespace App\Http\Controllers\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\ClassAttendanceSession;
use App\Services\ClassAttendanceService;

/** Student › Attendance — one tap: the time decides present (green) or late (yellow). */
class ClassAttendanceController extends Controller
{
    public function __construct(private ClassAttendanceService $svc) {}

    public function index()
    {
        $student = auth()->user();

        return view('student.attendance', [
            'open'    => $this->svc->studentOpenSessions($student),
            'history' => $this->svc->studentHistory($student),
        ]);
    }

    public function mark(ClassAttendanceSession $session)
    {
        try {
            $record = $this->svc->mark(auth()->user(), $session);
        } catch (ActionNotAllowed $e) {
            // If it just closed, its card is gone from the page — show the message at the top instead
            $key = $session->fresh()->isOpen() ? 'checkin_' . $session->id : 'general';
            return back()->withErrors([$key => $e->getMessage()]);
        }

        $label = $record->status === 'present' ? 'PRESENT' : 'LATE';

        return back()->with('success', "You are marked {$label} for {$session->subject?->name} at {$record->marked_at->format('g:i A')}.");
    }
}
