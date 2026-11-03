<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\TeacherActivationService;
use Illuminate\Http\Request;

/**
 * App: teacher activation from the Faculty Master List — same steps and checks as the web.
 * The API has no session, so step 1 returns an `activation_token` that steps 2 and resend send back.
 */
class TeacherActivationController extends Controller
{
    /** POST /api/teacher/activate */
    public function start(Request $request, TeacherActivationService $activation)
    {
        $data = $request->validate(TeacherActivationService::rules() + ['terms' => 'accepted'],
            TeacherActivationService::messages() + ['terms.accepted' => 'Please agree to the Terms of Use and Privacy Policy to activate your account.']);
        $result = $activation->start($data);

        return response()->json([
            'message'          => 'A 6-digit code was sent to your email.',
            'activation_token' => $result['token'],
            'email_masked'     => $result['email_masked'],
        ], 201);
    }

    /** POST /api/teacher/activate/verify — same response shape as /api/login */
    public function verify(Request $request, TeacherActivationService $activation)
    {
        $request->validate(['activation_token' => 'required|string|max:100', 'code' => 'required|digits:6']);

        $user = $activation->verify($request->activation_token, $request->code);
        $user->forceFill(['terms_accepted_at' => now()])->save();   // agreed on step 1
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
                'employee_id' => $user->employee_id,
                'status'      => $user->status,
                'terms_accepted' => $user->terms_accepted_at !== null,   // false: show the Terms screen (POST /api/terms/accept)
            ],
        ], 201);
    }

    /** POST /api/teacher/activate/resend */
    public function resend(Request $request, TeacherActivationService $activation)
    {
        $request->validate(['activation_token' => 'required|string|max:100']);
        $activation->resend($request->activation_token);

        return response()->json(['message' => 'A new code was sent.']);
    }
}
