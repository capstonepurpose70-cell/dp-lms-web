<?php

namespace App\Http\Controllers\Auth;


use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OtpService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    public function show()
    {
        // Countdown from the code's real expiry, not a fresh 5:00 on every reload
        // (e.g. after the rate-limit redirect the old code is still the one that counts).
        $expiresAt = OtpCode::where('user_id', session('otp_user_id'))
            ->where('purpose', 'login')
            ->where('used', false)
            ->latest()
            ->first()?->expires_at;

        return view('auth.otp', [
            'secondsLeft' => $expiresAt ? (int) max(0, now()->diffInSeconds($expiresAt, false)) : 0,
        ]);
    }

    public function verify(Request $request, TrustedDeviceService $devices)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('otp_user_id');
        $user   = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        $key = 'otp-verify:' . $userId;

if (RateLimiter::tooManyAttempts($key, 10)) {
    session()->forget(['otp_user_id', 'otp_email']);
    AuditLogService::log('OTP brute-force blocked', 'Auth', $user->email ?? '');
    return redirect()->route('login')
        ->with('error', 'Too many incorrect attempts. Please log in again.');
}

RateLimiter::hit($key, 300);

        $valid = app(OtpService::class)->verify($user, $request->otp, 'login');

        if (!$valid) {
            AuditLogService::log('OTP verification failed', 'Auth', $user->email);
            return back()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        // Remember-me flag captured back at the login step (before OTP).
        $remember = (bool) session('otp_remember', false);

        // Clear OTP session
        session()->forget(['otp_user_id', 'otp_email', 'otp_remember']);

        // Full login (honor "Remember me")
        auth()->login($user, $remember);
        $request->session()->regenerate();
        RateLimiter::clear($key);

        // "Remember this device" — no OTP on it for 30 days
        if ($request->boolean('trust_device')) {
            $devices->trust($user, $request);
        }

        AuditLogService::log('Login successful', 'Auth', "Role: {$user->role}");

        return redirect()->route($user->homeRoute());
    }

    public function resend()
    {
        $userId = session('otp_user_id');
        $user   = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired.');
        }

        try {
            app(OtpService::class)->send($user, 'login');
            return back()->with('success', 'A new OTP has been sent to your email.');
        } catch (ActionNotAllowed $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Could not resend OTP. Please wait a moment and try again.');
        }
    }
}