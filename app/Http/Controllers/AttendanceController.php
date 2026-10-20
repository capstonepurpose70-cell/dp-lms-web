<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FaceRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Device authentication — X-Device-Key dapat tugma sa ATTENDANCE_DEVICE_KEY.
     * Kapag WALANG key sa .env (local LAN testing), bukas — kaya DAPAT itakda
     * ang key sa production.
     */
    private function deviceAllowed(Request $request): bool
    {
        $deviceKey = (string) config('attendance.device_key');

        return $deviceKey === ''
            || hash_equals($deviceKey, (string) $request->header('X-Device-Key', ''));
    }

    // ESP32 / Pi calls this — device key, no user login
    public function store(Request $request)
    {
        if (!$this->deviceAllowed($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized device.'], 401);
        }

        $validated = $request->validate([
            'student_id'   => 'required|string|max:255',
            'student_name' => 'nullable|string|max:255',
            // Dati walang `exists` — maling ID mula sa device = FK error (500)
            'section_id'   => 'nullable|integer|exists:sections,id',
        ]);

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

        // Isang record lang kada estudyante kada araw — dati bawat scan ay
        // bagong row (10 scan = 10 "present" sa listahan).
        $already = Attendance::whereDate('attended_at', today())
            ->where(function ($q) use ($user, $key) {
                $q->where('student_id', $key);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if ($already) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance already recorded today.',
                'student' => $user?->name ?? $key,
                'status'  => $already->status,
            ], 200);
        }

        // PRESENT vs LATE — cutoff sa config/attendance.php ('late_after', default 07:30)
        $attendedAt = now();
        [$ch, $cm]  = array_pad(explode(':', (string) config('attendance.late_after', '07:30')), 2, '00');
        $status     = $attendedAt->greaterThan(now()->copy()->setTime((int) $ch, (int) $cm)) ? 'late' : 'present';

        Attendance::create([
            'user_id'      => $user?->id,
            'student_id'   => $key,
            'student_name' => $validated['student_name'] ?? $user?->name,
            'section_id'   => $validated['section_id'] ?? $user?->section_id,
            'status'       => $status,
            'source'       => 'iot',
            'attended_at'  => $attendedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded!',
            'student' => $user?->name ?? $key,
            'status'  => $status,
        ], 200);
    }

    // Teacher views attendance of their students only
    public function teacherIndex()
    {
        // Lahat ng section ng teacher — subject teacher, adviser, o assignment.
        $sectionIds = auth()->user()->teachingSectionIds();

        $students      = User::whereIn('id', User::studentIdsInSections($sectionIds))->get(['id', 'email']);
        $studentIds    = $students->pluck('id')->filter()->values();
        $studentEmails = $students->pluck('email')->filter()->values();

        // Robust kahit null/luma ang section_id ng device row.
        $attendances = Attendance::with('user')
            ->where(function ($q) use ($sectionIds, $studentIds, $studentEmails) {
                $q->whereIn('section_id', $sectionIds);
                if ($studentIds->isNotEmpty())    $q->orWhereIn('user_id', $studentIds);
                if ($studentEmails->isNotEmpty()) $q->orWhereIn('student_id', $studentEmails);
            })
            ->orderBy('attended_at', 'desc')
            ->paginate(30);

        $hasSections = $sectionIds->isNotEmpty();

        return view('teacher.attendance.index', compact('attendances', 'hasSections'));
    }

    /**
     * GET /api/attendance — huling 50 scan para sa device display.
     * Dati: walang auth at ibinabalik ang BUONG User (email, LRN, contact,
     * birthdate…) sa kahit sinong nasa internet. Ngayon: device key + piling field.
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
                $dir   = "faces/{$r->user_id}";
                $files = Storage::disk('public')->exists($dir)
                    ? Storage::disk('public')->files($dir)
                    : [];
                $images = collect($files)
                    ->filter(fn ($f) => preg_match('/\.(jpe?g|png)$/i', $f))
                    ->map(fn ($f) => url(Storage::url($f)))
                    ->values()
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
}
