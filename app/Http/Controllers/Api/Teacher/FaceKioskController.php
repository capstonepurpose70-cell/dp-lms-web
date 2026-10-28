<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\FaceKioskCode;
use App\Models\User;
use App\Services\FaceKioskService;
use Illuminate\Http\Request;

/**
 * App version of Teacher › Face Registration: the adviser shows a one-time QR on the phone and the
 * student holds it up to the Raspberry Pi kiosk. Same rules as the web page (Teacher\FaceKioskController).
 * The QR is returned as text so the app draws it itself (qr_flutter).
 */
class FaceKioskController extends Controller
{
    public function __construct(private FaceKioskService $kiosk) {}

    /** GET /api/teacher/face-kiosk — advisory students with their face status */
    public function index(Request $request)
    {
        $students = $this->kiosk->adviserStudents($request->user())->map(fn ($s) => [
            'id'          => $s->id,
            'name'        => $s->name,
            'lrn'         => $s->lrn,
            'grade_level' => $s->section?->grade_level,
            'section'     => $s->section?->name,
            'face_status' => $s->face_status,                 // approved | pending | rejected | null
            'blocked'     => (int) $s->face_warnings >= 3,    // only the admin can unblock
        ])->values();

        return response()->json([
            'students'   => $students,
            'registered' => $students->where('face_status', 'approved')->count(),
        ]);
    }

    /** POST /api/teacher/face-kiosk/{student} — a new one-time QR (cancels the student's older one) */
    public function issue(Request $request, User $student)
    {
        $teacher = $request->user();
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
            'qr_text'     => $r['token'],   // draw exactly this text as the QR
            'student'     => $student->name,
            'expires_at'  => $r['expires_at']->toIso8601String(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /** GET /api/teacher/face-kiosk/codes/{code} — waiting | registered | expired | cancelled */
    public function status(Request $request, int $code)
    {
        $row = FaceKioskCode::find($code);
        if (!$row) {
            return response()->json(['status' => 'cancelled']);   // replaced by a newer QR
        }
        if ($row->issued_by_role !== 'teacher' || (int) $row->issued_by_id !== (int) $request->user()->id) {
            return response()->json(['status' => 'forbidden'], 403);
        }

        return response()->json(['status' => $this->kiosk->status($row)]);
    }
}
