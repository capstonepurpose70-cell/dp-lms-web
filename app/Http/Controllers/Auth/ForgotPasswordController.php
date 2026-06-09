<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ForgotPasswordController extends Controller
{
    // ── Step 1: Show email form ───────────────────────────────────
    public function show()
    {
        return view('auth.forgot-password');
    }

    // ── Step 2: Send 6-digit code to email ───────────────────────
    public function submit(Request $request)
    {
$request->validate([
    'email' => 'required|email|exists:users,email',
], [
    'email.exists' => 'No account found with this email address.',
]);

        $key = 'reset:' . $request->email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'Too many attempts. Please wait a few minutes before trying again.');
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old reset codes for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Store hashed code
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($code),
            'created_at' => now(),
        ]);

        RateLimiter::hit($key, 300);

        // Send email with code
        Mail::send('emails.reset-code', [
            'code'  => $code,
            'email' => $request->email,
        ], function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Your DP-LMS Password Reset Code');
        });

        AuditLogService::log('Password reset code sent', 'Auth', $request->email);

        // Store email in session for next steps
        session(['reset_email' => $request->email]);

        return redirect()->route('password.verify')
            ->with('success', 'A 6-digit reset code has been sent to your email.');
    }

    // ── Step 3: Show code verification form ──────────────────────
    public function showVerify()
    {
        if (!session('reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.forgot-verify');
    }

    // ── Step 4: Verify the code ───────────────────────────────────
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start again.');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($request->code, $record->token)) {
            return back()->with('error', 'Invalid code. Please check your email and try again.');
        }

        // Check if code is older than 10 minutes
        if (now()->diffInMinutes($record->created_at) > 10) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->with('error', 'Code has expired. Please request a new one.');
        }

        // Mark as verified in session
        session(['reset_verified' => true]);

        return redirect()->route('password.reset.form');
    }

    // ── Step 5: Show new password form ───────────────────────────
    public function showReset()
    {
        if (!session('reset_email') || !session('reset_verified')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    // ── Step 6: Save new password ─────────────────────────────────
    public function reset(Request $request)
    {
  $request->validate([
    'password' => [
        'required',
        'min:8',
        'confirmed',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/',
    ],
], [
    'password.regex' => 'Password must have uppercase, lowercase, number, and special character.',
]);

        $email = session('reset_email');

        if (!$email || !session('reset_verified')) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start again.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Account not found.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Clean up
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['reset_email', 'reset_verified']);

        AuditLogService::log('Password reset successful', 'Auth', $email);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. You can now sign in with your new password.');
    }
} 