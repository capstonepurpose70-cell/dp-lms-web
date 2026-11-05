<?php
 
namespace App\Http\Controllers\Api;
 
use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\MobileLoginService;
use App\Services\PasswordResetService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
 
class AuthController extends Controller
{
    public function login(Request $request, TrustedDeviceService $devices, MobileLoginService $mobile)
    {
        // The field is still named "email" (do NOT rename — the deployed app sends it),
        // but it accepts an email, LRN or mobile number, same as the web.
        $request->validate([
            'email'        => 'required|string|max:254',
            'password'     => 'required',
            'device_token' => 'nullable|string|max:100',
        ]);

        // Shared with the web login: per account + IP against guessing, per IP against bots
        $identifier = trim((string) $request->email);
        $key        = 'login:' . sha1(mb_strtolower($identifier) . '|' . $request->ip());
        $ipKey      = 'login-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 50)) {
            AuditLogService::log('Login blocked — rate limit', 'Auth', $identifier);
            return response()->json(['message' => 'Too many login attempts. Please try again in 30 minutes.'], 429);
        }

        $user = User::resolveByIdentifier($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 1800);
            RateLimiter::hit($ipKey, 1800);
            AuditLogService::log('Failed login', 'Auth', $request->email);
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($refused = $this->refusal($user)) {
            return $refused;
        }

        RateLimiter::clear($key);

        if (!config('services.api_login_otp') || $devices->isTrustedToken($user, $request->device_token, $request)) {
            return response()->json($this->signIn($user));
        }

        // No email (some parents) on a new phone: nowhere to send a code.
        // The recovery code from the child is the proof instead, same as the web.
        if (empty($user->email)) {
            AuditLogService::log('Login blocked — new device, no email', 'Auth', $identifier, $user);
            return response()->json([
                'message' => 'This is a new device. Ask your child for a recovery code and use Parent recovery.',
                'status'  => 'new_device',
            ], 403);
        }

        $body = [
            'otp_required' => true,
            'otp_token'    => $mobile->begin($user),
            'email_hint'   => $this->maskEmail($user->email),
            'expires_in'   => 300,
            'message'      => 'We sent a 6-digit code to your email.',
        ];

        try {
            $mobile->sendCode($user);
        } catch (ActionNotAllowed $e) {
            // Sending is rate limited; the last emailed code still works until it expires
            $body['message'] = $e->getMessage();
        }

        return response()->json($body);
    }

    // POST /api/login/verify — the code from the email
    public function verifyLogin(Request $request, TrustedDeviceService $devices, MobileLoginService $mobile)
    {
        $data = $request->validate([
            'otp_token'       => 'required|string|max:100',
            'code'            => 'required|digits:6',
            'remember_device' => 'nullable|boolean',
        ]);

        try {
            $user = $mobile->verify($data['otp_token'], $data['code']);
        } catch (ActionNotAllowed $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        // The account may have been blocked during the code step
        if ($refused = $this->refusal($user)) {
            return $refused;
        }

        $body = $this->signIn($user);

        if ($request->boolean('remember_device')) {
            $body['device_token'] = $devices->issueToken($user, $request);
        }

        return response()->json($body);
    }

    // POST /api/login/resend — a new code for the same login
    public function resendLogin(Request $request, MobileLoginService $mobile)
    {
        $data = $request->validate(['otp_token' => 'required|string|max:100']);

        try {
            $mobile->resend($data['otp_token']);
        } catch (ActionNotAllowed $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json(['message' => 'We sent a new code to your email.', 'expires_in' => 300]);
    }

    /** Why this account may not sign in now, or null. */
    private function refusal(User $user): ?JsonResponse
    {
        if ($user->status !== 'approved') {
            AuditLogService::log('Login blocked (not approved)', 'Auth', $user->email, $user);
            // "inactive": a teacher who left the school (retired, resigned, transferred)
            $message = $user->status === 'inactive'
                ? 'This account is no longer active. Please contact the school.'
                : 'Account not yet approved.';

            return response()->json(['message' => $message, 'status' => $user->status], 403);
        }

        // Temporary password (old single "Create Teacher" accounts): no mobile token
        // until it is changed on the website.
        if ($user->must_change_password) {
            AuditLogService::log('Login blocked (temporary password)', 'Auth', $user->email, $user);
            return response()->json([
                'message' => 'Please log in on the website first and set a new password.',
            ], 403);
        }

        return null;
    }

    private function signIn(User $user): array
    {
        $token = $user->createToken('mobile-app')->plainTextToken;

        AuditLogService::log('Login', 'Auth', null, $user);

        return [
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'grade_level' => $user->grade_level,
                'status'      => $user->status,
                'terms_accepted' => $user->terms_accepted_at !== null,   // false: show the Terms screen (POST /api/terms/accept)
            ],
        ];
    }

    /** "juan.cruz@gmail.com" becomes "j***@gmail.com", so a stranger with the password learns little. */
    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 1) . '***@' . $domain;
    }

    public function logout(Request $request)
    {
        AuditLogService::log('Logout', 'Auth', null, $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function register(Request $request)
    {
        // No public registration.
        // Students: /api/activate (LRN + name + email code).
        // Parents:  /api/parent/activate (code from the child's account).
        return response()->json([
            'message' => 'Registration has been replaced. Students: use /api/activate. '
                . "Parents: use the code from your child's account at /api/parent/activate.",
        ], 410);
    }

    /**
     * POST /api/forgot-password  { email }
     * Same answer whether or not the account exists (no enumeration).
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