<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\FaceKioskCode;
use App\Models\User;
use App\Services\FaceKioskService;

/** Teacher › Face Registration — the ADVISER shows a one-time QR for each advisory student. */
class FaceKioskController extends Controller
{
    public function __construct(private FaceKioskService $kiosk) {}

    public function index()
    {
        return view('teacher.face-kiosk', [
            'students' => $this->kiosk->adviserStudents(auth()->user()),
        ]);
    }

    public function issue(User $student)
    {
        $teacher = auth()->user();
        if (!$this->kiosk->canTeacherIssue($teacher, $student)) {
            return response()->json(['ok' => false, 'message' => 'You can only register faces of students in your advisory section.'], 403);
        }

        try {
            $r = $this->kiosk->issue($student, 'teacher', $teacher->id);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'          => true,
            'code_id'     => $r['id'],
            'qr_svg'      => $r['qr_svg'],
            'student'     => $student->name,
            'expires_at'  => $r['expires_at']->toIso8601String(),
            'server_time' => now()->toIso8601String(),
            'status_url'  => route('teacher.face-kiosk.status', $r['id']),
        ]);
    }

    public function status(int $code)
    {
        $row = FaceKioskCode::find($code);
        if (!$row) {
            return response()->json(['status' => 'cancelled']);   // replaced by a newer QR
        }
        if ($row->issued_by_role !== 'teacher' || (int) $row->issued_by_id !== (int) auth()->id()) {
            return response()->json(['status' => 'forbidden'], 403);
        }

        return response()->json(['status' => $this->kiosk->status($row)]);
    }
}
