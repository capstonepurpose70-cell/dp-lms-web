<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    /** GET /api/student/face — current status + whether the student may register */
    public function show(Request $request)
    {
        $user = $request->user();
        $registration = FaceRegistration::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'enrolled'     => !is_null($user->section_id),
            'blocked'      => $user->face_warnings >= 3,
            'warnings'     => (int) $user->face_warnings,
            'status'       => $registration->status ?? null,          // pending|approved|rejected|null
            'images_count' => $registration->images_count ?? 0,
            'reject_reason' => $registration->reject_reason ?? null,
        ]);
    }

    /** POST /api/student/face — receive captured face images from the mobile app */
    public function store(Request $request)
    {
        $user = $request->user();

        // Must be enrolled in a section first
        if (is_null($user->section_id)) {
            return response()->json([
                'ok'      => false,
                'message' => 'You must be enrolled in a section before registering your face.',
            ], 403);
        }

        // Banned after 3 inappropriate-image warnings
        if ($user->face_warnings >= 3) {
            return response()->json([
                'ok'      => false,
                'message' => 'Face registration is blocked due to repeated violations. Please see the admin office.',
            ], 403);
        }

        $request->validate([
            'images'   => 'required|array|min:15|max:30',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048', // up to 2 MB each (phone photos)
        ]);

        // Replace any previous attempt (keep only the latest set)
        Storage::disk('public')->deleteDirectory("faces/{$user->id}");
        FaceRegistration::where('user_id', $user->id)->delete();

        $count = 0;
        foreach ($request->file('images') as $i => $file) {
            $file->storeAs(
                "faces/{$user->id}",
                'img_' . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . '.jpg',
                'public'
            );
            $count++;
        }

        FaceRegistration::create([
            'user_id'      => $user->id,
            'status'       => 'pending',
            'images_count' => $count,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => "Uploaded {$count} face images. Waiting for admin verification.",
        ]);
    }
}