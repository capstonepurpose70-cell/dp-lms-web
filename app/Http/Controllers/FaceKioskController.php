<?php

namespace App\Http\Controllers;

use App\Exceptions\ActionNotAllowed;
use App\Services\FaceKioskService;
use App\Support\DeviceKey;
use Illuminate\Http\Request;

/**
 * Raspberry Pi face kiosk endpoints (X-Device-Key, no user login).
 *   GET  /api/kiosk/face?code=DPLMS-FACE:...   → who is this QR for (does not use it)
 *   POST /api/kiosk/face  code + images[]       → save photos (approved) and use up the QR
 */
class FaceKioskController extends Controller
{
    public function __construct(private FaceKioskService $kiosk) {}

    public function lookup(Request $request)
    {
        if (!DeviceKey::valid($request)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized device.'], 401);
        }
        $data = $request->validate(['code' => 'required|string|max:100']);

        try {
            $code = $this->kiosk->lookup($data['code']);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }
        $s = $code->student;

        return response()->json([
            'ok'         => true,
            'student'    => [
                'id'          => $s->id,
                'name'        => $s->name,
                'grade_level' => (string) ($s->section?->grade_level ?? $s->grade_level),
                'section'     => $s->section?->name,
            ],
            'expires_at' => $code->expires_at->toIso8601String(),
            'min_images' => FaceKioskService::MIN_IMAGES,
            'max_images' => FaceKioskService::MAX_IMAGES,
        ]);
    }

    public function register(Request $request)
    {
        if (!DeviceKey::valid($request)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized device.'], 401);
        }
        $request->validate([
            'code'     => 'required|string|max:100',
            'images'   => 'required|array|min:' . FaceKioskService::MIN_IMAGES . '|max:' . FaceKioskService::MAX_IMAGES,
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $student = $this->kiosk->register($request->input('code'), $request->file('images'), $request->ip());
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'      => true,
            'message' => "Face registered for {$student->name}.",
            'student' => ['id' => $student->id, 'name' => $student->name, 'label' => $student->email],
        ]);
    }
}
