<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\TeacherSubject;
use App\Models\TeacherAssignment;
use App\Models\FaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    // ESP32 calls this — no auth needed
    public function store(Request $request)
    {
        // Device authentication — only a device with the matching X-Device-Key may
        // push attendance. Enforced ONLY when a key is configured, so local LAN
        // testing without a key still works (set ATTENDANCE_DEVICE_KEY in .env to enable).
        $deviceKey = config('attendance.device_key');
        if (!empty($deviceKey)
            && !hash_equals((string) $deviceKey, (string) $request->header('X-Device-Key', ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized device.',
            ], 401);
        }

        $validated = $request->validate([
            'student_id'  => 'required|string',
            'student_name'=> 'nullable|string',
            'section_id'  => 'nullable|integer',
        ]);

        // LRN removed — match the student by EMAIL or by account ID.
        $key = $validated['student_id'];
        $user = User::where('role', 'student')
                    ->where(function ($q) use ($key) {
                        $q->where('email', $key)
                          ->orWhere('id', $key);
                    })
                    ->first();

        $attendance = Attendance::create([
            'user_id'      => $user?->id,
            'student_id'   => $validated['student_id'],
            'student_name' => $validated['student_name'] ?? $user?->name,
            'section_id'   => $validated['section_id'] ?? $user?->section_id,
            'status'       => 'present',
            'source'       => 'iot',
            'attended_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded!',
            'student' => $user?->name ?? $validated['student_id'],
        ], 200);
    }

    // Teacher views attendance of their students only
    public function teacherIndex()
    {
        $teacher = auth()->user();

        // All sections this teacher is linked to — as a SUBJECT teacher or as an ADVISER.
        // A brand-new teacher with no assignments yet will have an empty list,
        // so they correctly see NO attendance until faculty assigns them a section.
        $sectionIds = TeacherSubject::where('user_id', $teacher->id)->pluck('section_id')
            ->merge(TeacherAssignment::where('user_id', $teacher->id)->pluck('section_id'))
            ->filter()
            ->unique()
            ->values();

        // The students currently in those sections (the teacher's roster).
        $students = User::where('role', 'student')
            ->whereIn('section_id', $sectionIds)
            ->get(['id', 'email']);
        $studentIds    = $students->pluck('id')->filter()->values();
        $studentEmails = $students->pluck('email')->filter()->values();

        // Match an attendance row if ANY of these is true:
        //   - its section_id is one of the teacher's sections, OR
        //   - it is linked to one of the teacher's students (user_id), OR
        //   - its student_id (email pushed by the device) is one of those students.
        // This is robust even if the device row has a null/stale section_id.
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

    // API — returns JSON list
    public function index()
    {
        return response()->json(
            Attendance::with('user')
                ->orderBy('attended_at', 'desc')
                ->take(50)
                ->get()
        );
    }

    // API — Raspberry Pi pulls all APPROVED student faces to train its model.
    // GET /api/faces/approved   (optional X-Device-Key header)
    public function approvedFaces(Request $request)
    {
        $deviceKey = config('attendance.device_key');
        if (!empty($deviceKey)
            && !hash_equals((string) $deviceKey, (string) $request->header('X-Device-Key', ''))) {
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
                    'user_id' => $r->user_id,
                    'label'   => $r->user->email,
                    'name'    => $r->user->name,
                    'reviewed_at' => optional($r->reviewed_at ?? $r->updated_at)->toIso8601String(),
                    'images'  => $images,
                ];
            })
            ->filter(fn ($f) => count($f['images']) > 0)
            ->values();

        return response()->json(['faces' => $faces]);
    }
}