<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    public function show()
    {
        return view('auth.otp');
    }

    public function verify(Request $request)
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
        RateLimiter::clear($key);

        AuditLogService::log('Login successful', 'Auth', "Role: {$user->role}");

        return match($user->role) {
            'student' => redirect()->route('student.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent'  => redirect()->route('parent.dashboard'),
            default   => redirect()->route('login'),
        };
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
        } catch (\Exception $e) {
            return back()->with('error', 'Could not resend OTP. Please wait a moment and try again.');
        }
    }
}