<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function submit(Request $request)
    {
        // Tinatanggap ang EMAIL o LRN sa iisang field. Pinanatili ang field
        // name na "email" para hindi masira ang mga existing na form/app.
        $request->validate([
            'email'    => 'required|string|max:254',
            'password' => 'required',
        ], [
            'email.required' => 'Email or LRN is required.',
        ]);

        $identifier = trim((string) $request->email);

        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            AuditLogService::log('Login blocked — rate limit', 'Auth', $identifier);
            return back()->with('error', 'Too many login attempts. Please try again in 30 minutes.');
        }

        $user = User::resolveByIdentifier($identifier);

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 1800);
            AuditLogService::log('Failed login attempt', 'Auth', $identifier);
            return back()->with('error', 'Invalid email/LRN or password.')->withInput();
        }

        if ($user->status === 'pending') {
            return back()->with('error', 'Your account is awaiting administrator approval.');
        }

        if ($user->status === 'rejected') {
            return back()->with('error', 'Your registration was not approved. Please contact the school.');
        }

        RateLimiter::clear($key);

        // Store user temporarily in session — require OTP before full login
        session([
            'otp_user_id'  => $user->id,
            'otp_email'    => $user->email,
            'otp_remember' => $request->boolean('remember'),
        ]);

        // Send OTP
        app(OtpService::class)->send($user, 'login');

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