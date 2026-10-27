<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OtpService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function submit(Request $request, TrustedDeviceService $devices)
    {
        // Tinatanggap ang EMAIL, LRN, o MOBILE sa iisang field. Pinanatili ang field
        // name na "email" para hindi masira ang mga existing na form/app.
        $request->validate([
            'email'    => 'required|string|max:254',
            'password' => 'required',
        ], [
            'email.required' => 'Email, LRN, or mobile number is required.',
        ]);

        $identifier = trim((string) $request->email);

        // Dalawang limit:
        //  - kada ACCOUNT + IP (5 / 30 min) — panlaban sa paghula ng password
        //  - kada IP (50 / 30 min) — panlaban sa bot; maluwag dahil iisang IP
        //    ang buong school Wi-Fi (dati: 5 mali ng isang bata = lock ang klase)
        $key   = 'login:' . sha1(mb_strtolower($identifier) . '|' . $request->ip());
        $ipKey = 'login-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 50)) {
            AuditLogService::log('Login blocked — rate limit', 'Auth', $identifier);
            return back()->with('error', 'Too many login attempts. Please try again in 30 minutes.');
        }

        $user = User::resolveByIdentifier($identifier);

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 1800);
            RateLimiter::hit($ipKey, 1800);
            AuditLogService::log('Failed login attempt', 'Auth', $identifier);
            return back()->with('error', 'Invalid email/LRN/mobile or password.')->withInput();
        }

        if ($user->status === 'pending') {
            return back()->with('error', 'Your account is awaiting administrator approval.');
        }

        if ($user->status === 'rejected') {
            return back()->with('error', 'Your registration was not approved. Please contact the school.');
        }

        RateLimiter::clear($key);

        // Trusted device — laktaw ang OTP
        if ($devices->isTrusted($user, $request)) {
            auth()->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            AuditLogService::log('Login successful (trusted device)', 'Auth', "Role: {$user->role}");

            return redirect()->route($user->homeRoute());
        }

        // Walang email (hal. magulang) at bagong device — walang mapagpapadalhan
        // ng OTP. Ang recovery code mula sa anak ang patunay.
        if (empty($user->email)) {
            AuditLogService::log('Login blocked — new device, no email', 'Auth', $identifier);
            return back()->withInput()->with('error',
                'This is a new device. Ask your child for a recovery code (from their DP-LMS account) '
                . 'and use it on the "Parent recovery" page.');
        }

        // Store user temporarily in session — require OTP before full login
        session([
            'otp_user_id'  => $user->id,
            'otp_email'    => $user->email,
            'otp_remember' => $request->boolean('remember'),
        ]);

        // Send OTP
        try {
            app(OtpService::class)->send($user, 'login');
        } catch (ActionNotAllowed $e) {
            // Naka-limit ang pagpapadala — gumagana pa ang huling code sa email hangga't hindi expired.
            AuditLogService::log('Login code not sent — rate limit', 'Auth', $identifier);
            return redirect()->route('otp.show')->with('error', $e->getMessage());
        }

        return redirect()->route('otp.show');
    }

    public function logout(Request $request)
    {
        AuditLogService::log('Logout', 'Auth');
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}