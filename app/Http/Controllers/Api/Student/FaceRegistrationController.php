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
        // Enrolled if the student has an active enrollment OR a section assigned
        $section = $user->studentEnrollment?->section ?? $user->section;
        $registration = FaceRegistration::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'enrolled'      => !is_null($section),
            'blocked'       => $user->face_warnings >= 3,
            'warnings'      => (int) $user->face_warnings,
            'status'        => $registration->status ?? null, // pending|approved|rejected|null
            'images_count'  => $registration->images_count ?? 0,
            'reject_reason' => $registration->reject_reason ?? null,
        ]);
    }

    /** POST /api/student/face — receive captured face images from the mobile app */
    public function store(Request $request)
    {
        $user = $request->user();

        // Must be enrolled (active enrollment OR assigned section) first
        $section = $user->studentEnrollment?->section ?? $user->section;
        if (is_null($section)) {
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

        // The mobile app now captures 2 photos after a liveness (blink) check.
        // min:1 keeps it resilient (a single good frontal photo is still usable
        // by the Raspberry Pi), max:30 still allows richer sets if you raise it.
        $request->validate([
            'images'   => 'required|array|min:1|max:30',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048', // up to 2 MB each
        ]);

        $files = $request->file('images');

        // Store into a fresh temp dir first, then swap — so a mid-upload failure
        // never wipes the student's previously approved photos (important: the
        // Pi reads these for recognition).
        $finalDir = "faces/{$user->id}";
        $tmpDir   = "faces/tmp_{$user->id}_" . time();

        $count = 0;
        try {
            foreach ($files as $i => $file) {
                $file->storeAs(
                    $tmpDir,
                    'img_' . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . '.jpg',
                    'public'
                );
                $count++;
            }

            // Swap: remove old set, move the new one into place.
            Storage::disk('public')->deleteDirectory($finalDir);
            // Move each file from tmp -> final (Storage has no atomic rename dir).
            foreach (Storage::disk('public')->files($tmpDir) as $path) {
                $name = basename($path);
                Storage::disk('public')->move($path, "{$finalDir}/{$name}");
            }
            Storage::disk('public')->deleteDirectory($tmpDir);
        } catch (\Throwable $e) {
            // Clean up the temp dir on any failure; keep old photos intact.
            Storage::disk('public')->deleteDirectory($tmpDir);
            return response()->json([
                'ok'      => false,
                'message' => 'Could not save your photos. Please try again.',
            ], 500);
        }

        // Replace the DB record (keep only the latest attempt).
        FaceRegistration::where('user_id', $user->id)->delete();
        FaceRegistration::create([
            'user_id'      => $user->id,
            'status'       => 'pending',
            'images_count' => $count,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => "Uploaded {$count} face image(s). Waiting for admin verification.",
        ]);
    }
}