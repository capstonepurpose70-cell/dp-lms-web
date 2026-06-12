<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    /** Camera page + current status */
    public function show()
    {
        $user = auth()->user();
        $registration = FaceRegistration::where('user_id', $user->id)->latest()->first();

        return view('student.face-register', [
            'registration' => $registration,
            'blocked'      => $user->face_warnings >= 3,
            'warnings'     => $user->face_warnings,
        ]);
    }

    /** Receive 15–30 face-cropped images from the browser */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Banned from face registration after 3 warnings
        if ($user->face_warnings >= 3) {
            return response()->json([
                'ok'      => false,
                'message' => 'Face registration is blocked due to repeated violations. Please see the admin office.',
            ], 403);
        }

        $request->validate([
            'images'   => 'required|array|min:15|max:30',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:600', // max 600 KB each
        ]);

        // Replace any previous attempt (only the latest set is kept)
        Storage::disk('public')->deleteDirectory("faces/{$user->id}");
        FaceRegistration::where('user_id', $user->id)->delete();

        $count = 0;
        foreach ($request->file('images') as $i => $file) {
            $file->storeAs("faces/{$user->id}", 'img_' . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . '.jpg', 'public');
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