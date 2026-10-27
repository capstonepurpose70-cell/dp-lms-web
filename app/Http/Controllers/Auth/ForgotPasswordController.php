<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

/**
 * Web forgot-password: email → 6-digit code → new password.
 * The rules (expiry, guess limit, no enumeration) live in
 * PasswordResetService, shared with the mobile API.
 */
class ForgotPasswordController extends Controller
{
    // ── Step 1: Show email form ───────────────────────────────────
    public function show()
    {
        return view('auth.forgot-password');
    }

    // ── Step 2: Send 6-digit code to email ───────────────────────
    public function submit(Request $request, PasswordResetService $resets)
    {
        $request->validate(['email' => 'required|email|max:254']);

        if (!$resets->sendCode($request->email)) {
            return back()->with('error', 'Too many attempts. Please wait a few minutes before trying again.');
        }

        session(['reset_email' => PasswordResetService::normalize($request->email)]);

        return redirect()->route('password.verify')
            ->with('success', 'If an account exists for that email, a 6-digit reset code has been sent.');
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
    public function verify(Request $request, PasswordResetService $resets)
    {
        $request->validate(['code' => 'required|digits:6']);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start again.');
        }

        if ($error = $resets->checkCode($email, $request->code)) {
            return back()->with('error', $error);
        }

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
    public function reset(Request $request, PasswordResetService $resets)
    {
        $request->validate(['password' => PasswordResetService::passwordRules()], [
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $email = session('reset_email');
        if (!$email || !session('reset_verified')) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start again.');
        }

        $user = $resets->reset($email, $request->password);
        session()->forget(['reset_email', 'reset_verified']);

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Account not found.');
        }

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. You can now sign in with your new password.');
    }
}
