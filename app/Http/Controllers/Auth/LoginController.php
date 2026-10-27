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
        // Accepts an EMAIL, LRN or MOBILE number in one field. The field keeps the
        // name "email" so existing forms and the app keep working.
        $request->validate([
            'email'    => 'required|string|max:254',
            'password' => 'required',
        ], [
            'email.required' => 'Email, LRN, or mobile number is required.',
        ]);

        $identifier = trim((string) $request->email);

        // Two limits:
        //  - per ACCOUNT + IP (5 / 30 min) — against password guessing
        //  - per IP (50 / 30 min) — against bots; generous because the whole
        //    school Wi-Fi shares one IP (one child's 5 mistakes must not lock the class)
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

        // Trusted device — skip the OTP
        if ($devices->isTrusted($user, $request)) {
            auth()->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            AuditLogService::log('Login successful (trusted device)', 'Auth', "Role: {$user->role}");

            return redirect()->route($user->homeRoute());
        }

        // No email (e.g. a parent) on a new device — there is nowhere to send
        // an OTP. The recovery code from the child is the proof instead.
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
            // Sending is rate limited — the last emailed code still works until it expires.
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