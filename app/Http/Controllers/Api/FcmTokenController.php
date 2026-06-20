<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * POST /api/fcm-token  (auth:sanctum)
     * Saves (or re-assigns) a device token for the logged-in user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|max:20',
        ]);

        // If this token already exists, point it to the current user.
        FcmToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? null,
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * DELETE /api/fcm-token  (auth:sanctum)
     * Removes a token (e.g. on logout).
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        FcmToken::where('token', $data['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}