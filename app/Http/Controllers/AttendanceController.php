<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FaceRegistration;
use App\Models\GateScan;
use App\Models\User;
use App\Services\ParentNotifier;
use App\Support\FacePhotos;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    /**
     * Device authentication — X-Device-Key must match ATTENDANCE_DEVICE_KEY.
     * No key in .env: open ONLY on local (XAMPP testing). In production it is
     * CLOSED — otherwise anyone could post fake attendance and download
     * every face photo.
     */
    private function deviceAllowed(Request $request): bool
    {
        return \App\Support\DeviceKey::valid($request);
    }

    // ESP32 / Pi calls this — device key, no user login
    public function store(Request $request, ParentNotifier $notifier)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized device.'], 401);
        }

        $validated = $request->validate([
            'student_id'   => 'required|string|max:255',
            'student_name' => 'nullable|string|max:255',
            // exists: an unknown id from the device would otherwise fail on the foreign key (500)
            'section_id'   => 'nullable|integer|exists:sections,id',
            // Gate camera extras: similarity of the match, which device, and when the face was seen
            // (a scan queued while the server was unreachable keeps its real time).
            'score'        => 'nullable|numeric|between:0,1',
            'device'       => 'nullable|string|max:64',
            'scanned_at'   => 'nullable|date',
        ]);

        $attendedAt = $this->scanTime($validated['scanned_at'] ?? null);
        if (! $attendedAt) {
            return response()->json([
                'success' => false,
                'message' => 'scanned_at must be within the last 2 days.',
            ], 422);
        }

        // Match the student by EMAIL or by account ID.
        $key  = $validated['student_id'];
        $user = User::where('role', 'student')
            ->where(function ($q) use ($key) {
                $q->where('email', $key);
                if (ctype_digit($key)) {
                    $q->orWhere('id', (int) $key);
                }
            })
            ->first();

        // One record per student per day — repeated scans must not add
        // another "present" row each time.
        $already = Attendance::whereDate('attended_at', $attendedAt->toDateString())
            ->where(function ($q) use ($user, $key) {
                $q->where('student_id', $key);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if ($already) {
            $this->logScan($user, 'repeat', $validated, $attendedAt, $already->id);

            return response()->json([
                'success' => true,
                'message' => 'Attendance already recorded today.',
                'student' => $user?->name ?? $key,
                'status'  => $already->status,
            ], 200);
        }

        // PRESENT vs LATE — cutoff in config/attendance.php ('late_after', default 07:30) on the day of the scan
        [$ch, $cm] = array_pad(explode(':', (string) config('attendance.late_after', '07:30')), 2, '00');
        $status    = $attendedAt->greaterThan($attendedAt->copy()->setTime((int) $ch, (int) $cm)) ? 'late' : 'present';

        $attendance = Attendance::create([
            'user_id'      => $user?->id,
            'student_id'   => $key,
            'student_name' => $validated['student_name'] ?? $user?->name,
            'section_id'   => $validated['section_id'] ?? $user?->section_id,
            'status'       => $status,
            'source'       => 'iot',
            'attended_at'  => $attendedAt,
        ]);

        $this->logScan($user, 'recognized', $validated, $attendedAt, $attendance->id);

        // 🔔 "Juan arrived at school · 7:12 AM" to the parent (in-app + FCM push).
        // Only the first scan of the day gets here (deduped above), so one
        // notification per day. A failed push never breaks the scan.
        if ($user) {
            $notifier->childArrived($user, $attendance);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded!',
            'student' => $user?->name ?? $key,
            'status'  => $status,
        ], 200);
    }

    // (Teacher web page for gate scans removed — teachers now use Class Attendance:
    //  Teacher\ClassAttendanceController. Gate scans still notify parents via store().)

    /**
     * GET /api/attendance — last 50 scans for the device display.
     * Device key required, and only the fields the display needs (no email,
     * LRN, contact number or birthdate).
     */
    public function index(Request $request)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['message' => 'Unauthorized device.'], 401);
        }

        return response()->json(
            Attendance::with('user:id,name')
                ->orderBy('attended_at', 'desc')
                ->take(50)
                ->get()
                ->map(fn ($a) => [
                    'id'           => $a->id,
                    'student_name' => $a->student_name ?? $a->user?->name,
                    'status'       => $a->status,
                    'source'       => $a->source,
                    'attended_at'  => $a->attended_at?->toDateTimeString(),
                ])
        );
    }

    // API — Raspberry Pi pulls all APPROVED student faces to train its model.
    // GET /api/faces/approved   (X-Device-Key header)
    public function approvedFaces(Request $request)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $faces = FaceRegistration::with('user')
            ->where('status', 'approved')
            ->get()
            ->filter(fn ($r) => $r->user
                && $r->user->role === 'student'
                && $r->user->status === 'approved')
            ->map(function ($r) {
                // Private photos: the device downloads each one with its key (facePhoto below)
                $images = collect(FacePhotos::names($r->user_id))
                    ->map(fn ($name) => url("/api/faces/{$r->user_id}/{$name}"))
                    ->all();

                return [
                    'user_id'     => $r->user_id,
                    'label'       => $r->user->email,
                    'name'        => $r->user->name,
                    'reviewed_at' => optional($r->reviewed_at ?? $r->updated_at)->toIso8601String(),
                    'images'      => $images,
                ];
            })
            ->filter(fn ($f) => count($f['images']) > 0)
            ->values();

        return response()->json(['faces' => $faces]);
    }

    /**
     * GET /api/faces/{user}/{file} — one photo of an approved student, for the gate camera.
     * Same rule as approvedFaces(): approved face set of an approved student, or nothing.
     */
    public function facePhoto(Request $request, int $user, string $file)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $approved = FaceRegistration::where('user_id', $user)->where('status', 'approved')
            ->whereHas('user', fn ($q) => $q->where('role', 'student')->where('status', 'approved'))
            ->exists();

        return ($approved ? FacePhotos::response($user, $file) : null)
            ?? response()->json(['message' => 'Not found.'], 404);
    }

    /**
     * POST /api/gate/unknown — the gate camera saw a face it could not match.
     * Only logged (score, device, time) for the accuracy figures; no image is sent or kept.
     */
    public function unknownFace(Request $request)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['message' => 'Unauthorized device.'], 401);
        }

        $validated = $request->validate([
            'score'      => 'nullable|numeric|between:0,1',
            'device'     => 'nullable|string|max:64',
            'scanned_at' => 'nullable|date',
        ]);

        $at = $this->scanTime($validated['scanned_at'] ?? null);
        if (! $at) {
            return response()->json(['message' => 'scanned_at must be within the last 2 days.'], 422);
        }

        $this->logScan(null, 'unknown', $validated, $at);

        return response()->json(['success' => true]);
    }

    /**
     * When the face was seen. Missing = now. A queued scan may be up to 2 days old; a device clock
     * slightly ahead of the server is clamped to now. Anything else is refused (null).
     */
    private function scanTime(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return now();
        }

        try {
            $at = Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }

        if ($at->lt(now()->subDays(2)) || $at->gt(now()->addMinutes(5))) {
            return null;
        }

        return $at->gt(now()) ? now() : $at;
    }

    private function logScan(?User $user, string $result, array $data, Carbon $at, ?int $attendanceId = null): void
    {
        GateScan::create([
            'user_id'       => $user?->id,
            'result'        => $result,
            'score'         => isset($data['score']) ? round((float) $data['score'], 4) : null,
            'device'        => $data['device'] ?? null,
            'attendance_id' => $attendanceId,
            'scanned_at'    => $at,
        ]);
    }
}
