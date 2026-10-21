<?php

namespace App\Http\Controllers\Student;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use App\Services\FaceRegistrationService;
use Illuminate\Http\Request;

class FaceRegistrationController extends Controller
{
    /** Camera page + current status */
    public function show()
    {
        $user         = auth()->user();
        $registration = FaceRegistration::where('user_id', $user->id)->latest()->first();

        // Kiosk-only (default): status page, no camera / upload
        if (!FaceRegistrationService::selfUploadEnabled()) {
            return view('student.face-status', [
                'registration' => $registration,
                'blocked'      => $user->face_warnings >= 3,
                'enrolled'     => $user->studentSectionId() !== null,
                'adviser'      => \App\Models\Section::with('adviser:id,name')->find($user->studentSectionId())?->adviser,
            ]);
        }

        return view('student.face-register', [
            'registration' => $registration,
            'blocked'      => $user->face_warnings >= 3,
            'warnings'     => $user->face_warnings,
            'enrolled'     => $user->studentSectionId() !== null,
        ]);
    }

    /** Receive 15–30 face-cropped images from the browser */
    public function store(Request $request, FaceRegistrationService $faces)
    {
        $user = auth()->user();

        try {
            $faces->assertSelfUploadAllowed();
            $faces->assertCanRegister($user);
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        $request->validate([
            'images'   => 'required|array|min:15|max:30',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:600', // max 600 KB each
        ]);

        try {
            $count = $faces->replaceImages($user, $request->file('images'));
        } catch (ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'      => true,
            'message' => "Uploaded {$count} face images. Waiting for admin verification.",
        ]);
    }
}
