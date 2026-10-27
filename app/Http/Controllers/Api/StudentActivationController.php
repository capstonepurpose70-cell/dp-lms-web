<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\StudentActivationService;
use Illuminate\Http\Request;

/**
 * Mobile (Flutter) version of student activation.
 * The API has no session, so step 1 returns an `activation_token`
 * that the app sends back to verify/resend.
 */
class StudentActivationController extends Controller
{
    /** POST /api/activate */
    public function start(Request $request, StudentActivationService $activation)
    {
        $data   = $request->validate(StudentActivationService::rules(), StudentActivationService::messages());
        $result = $activation->start($data);

        return response()->json([
            'message'          => 'A 6-digit code was sent to your email.',
            'activation_token' => $result['token'],
            'email_masked'     => $result['email_masked'],
            'expires_at'       => $result['expires_at']->toIso8601String(),
        ], 201);
    }

    /** POST /api/activate/verify — same response shape as /api/login */
    public function verify(Request $request, StudentActivationService $activation)
    {
        $request->validate([
            'activation_token' => 'required|string|max:100',
            'code'             => 'required|digits:6',
        ]);

        $user  = $activation->verify($request->activation_token, $request->code);
        $token = $user->createToken('mobile-app')->plainTextToken;

        AuditLogService::log('Login (activation)', 'Auth', null, $user);

        return response()->json([
            'message' => 'Account activated.',
            'token'   => $token,
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'grade_level' => $user->grade_level,
                'status'      => $user->status,
            ],
        ], 201);
    }

    /** POST /api/activate/resend */
    public function resend(Request $request, StudentActivationService $activation)
    {
        $request->validate(['activation_token' => 'required|string|max:100']);

        $activation->resend($request->activation_token);

        return response()->json(['message' => 'A new code was sent.']);
    }
}
