<?php

namespace App\Http\Controllers\Api\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use App\Services\FaceRegistrationService;
use Illuminate\Http\Request;

class FaceRegistrationController extends Controller
{
    /** GET /api/student/face — current status + whether the student may register */
    public function show(Request $request)
    {
        $user         = $request->user();
        $registration = FaceRegistration::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'enrolled'      => $user->studentSectionId() !== null,
            'blocked'       => $user->face_warnings >= 3,
            'warnings'      => (int) $user->face_warnings,
            'status'        => $registration->status ?? null, // pending|approved|rejected|null
            'images_count'  => $registration->images_count ?? 0,
            'reject_reason' => $registration->reject_reason ?? null,
        ]);
    }

    /** POST /api/student/face — receive captured face images from the mobile app */
    public function store(Request $request, FaceRegistrationService $faces)
    {
        $user = $request->user();

        try {
            $faces->assertCanRegister($user);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        // The mobile app captures 2 photos after a liveness (blink) check.
        // min:1 keeps it resilient; max:30 allows richer sets.
        $request->validate([
            'images'   => 'required|array|min:1|max:30',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048', // up to 2 MB each
        ]);

        try {
            $count = $faces->replaceImages($user, $request->file('images'));
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'      => true,
            'message' => "Uploaded {$count} face image(s). Waiting for admin verification.",
        ]);
    }
}
