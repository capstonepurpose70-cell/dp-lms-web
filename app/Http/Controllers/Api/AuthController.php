<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
 
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Field name "email" pa rin (HUWAG palitan — gamit ng naka-deploy na app),
        // pero tumatanggap na ng email, LRN, o mobile number, kapareho ng web.
        $request->validate([
            'email'    => 'required|string|max:254',
            'password' => 'required',
        ]);

        $user = User::resolveByIdentifier($request->email);
 
        if (!$user || !Hash::check($request->password, $user->password)) {
            AuditLogService::log('Failed login', 'Auth', $request->email);
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
 
        if ($user->status !== 'approved') {
            AuditLogService::log('Login blocked (not approved)', 'Auth', $user->email, $user);
            return response()->json(['message' => 'Account not yet approved.'], 403);
        }
 
        $token = $user->createToken('mobile-app')->plainTextToken;
 
        AuditLogService::log('Login', 'Auth', null, $user);
 
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'grade_level' => $user->grade_level,
                'status'      => $user->status,
            ],
        ]);
    }
 
    public function logout(Request $request)
    {
        AuditLogService::log('Logout', 'Auth', null, $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
 
    public function register(Request $request)
    {
        // Wala nang public registration.
        // Estudyante: /api/activate (LRN + pangalan + email code).
        // Magulang:   /api/parent/activate (code mula sa account ng anak).
        return response()->json([
            'message' => 'Registration has been replaced. Students: use /api/activate. '
                . "Parents: use the code from your child's account at /api/parent/activate.",
        ], 410);
    }

    /**
     * POST /api/forgot-password  { email }
     * Dati: stub na nagsasabing "sent" pero walang ipinapadala.
     * Parehong sagot may account man o wala (walang enumeration).
     */
    public function forgotPassword(Request $request, PasswordResetService $resets)
    {
        $request->validate(['email' => 'required|email|max:254']);

        if (!$resets->sendCode($request->email)) {
            return response()->json(['message' => 'Too many attempts. Please wait a few minutes.'], 429);
        }

        return response()->json([
            'message'         => 'If an account exists for that email, a 6-digit reset code has been sent.',
            'expires_minutes' => PasswordResetService::CODE_MINUTES,
        ]);
    }

    /** POST /api/forgot-password/reset  { email, code, password, password_confirmation } */
    public function resetPassword(Request $request, PasswordResetService $resets)
    {
        $data = $request->validate([
            'email'    => 'required|email|max:254',
            'code'     => 'required|digits:6',
            'password' => PasswordResetService::passwordRules(),
        ]);

        if ($error = $resets->checkCode($data['email'], $data['code'])) {
            throw ValidationException::withMessages(['code' => $error]);
        }

        if (!$resets->reset($data['email'], $data['password'])) {
            throw ValidationException::withMessages(['email' => 'Account not found.']);
        }

        return response()->json(['message' => 'Password reset successfully. You can now log in.']);
    }
}