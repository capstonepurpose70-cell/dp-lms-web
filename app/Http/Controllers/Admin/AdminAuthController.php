<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    private const MAX_PER_IP    = 5;
    private const MAX_PER_EMAIL = 10;
    private const LOCK_SECONDS  = 1800;   // 30 minutes

    public function show(Request $request)
    {
        // The page shows the real time left (also after a reload), taken from the same limiter the
        // form post checks — never a client-side guess.
        $ipKey = $this->ipKey($request);
        $wait  = RateLimiter::tooManyAttempts($ipKey, self::MAX_PER_IP) ? RateLimiter::availableIn($ipKey) : 0;

        return view('admin.login', [
            'lockSeconds' => max($wait, (int) session('lockout_seconds', 0)),
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // ── 1. Rate limit check FIRST (per IP and per email) ──
        // The lock lasts as long as the page says (30 minutes); otherwise the admin password could be
        // guessed 5 times every few seconds.
        $key      = $this->ipKey($request);
        $emailKey = 'admin-login-email:' . mb_strtolower((string) $request->email);

        if ($wait = $this->lockedFor($key, $emailKey)) {
            AuditLogService::log('Admin login blocked — rate limit', 'Auth', $request->email);
            return back()->with('lockout_seconds', $wait);
        }

        // ── 2. Then the lookup and password check ──
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            RateLimiter::hit($emailKey, self::LOCK_SECONDS);
            AuditLogService::log('Failed admin login', 'Auth', $request->email);

            if ($wait = $this->lockedFor($key, $emailKey)) {
                return back()->with('lockout_seconds', $wait)->withInput($request->only('email'));
            }

            $left    = RateLimiter::remaining($key, self::MAX_PER_IP);
            $message = 'Incorrect email or password.';
            if ($left <= 3) {
                $message .= ' ' . $left . ' ' . ($left === 1 ? 'attempt' : 'attempts') . ' left before sign-in is paused for 30 minutes.';
            }

            return back()->with('error', $message)->withInput($request->only('email'));
        }

        // ── 3. Success ──
        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();   // against session fixation
        RateLimiter::clear($key);
        RateLimiter::clear($emailKey);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('signed_out', true);
    }

    private function ipKey(Request $request): string
    {
        return 'admin-login:' . $request->ip();
    }

    /** Seconds until sign-in is allowed again (0 = not locked). */
    private function lockedFor(string $ipKey, string $emailKey): int
    {
        $wait = 0;
        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_PER_IP)) {
            $wait = RateLimiter::availableIn($ipKey);
        }
        if (RateLimiter::tooManyAttempts($emailKey, self::MAX_PER_EMAIL)) {
            $wait = max($wait, RateLimiter::availableIn($emailKey));
        }

        return max(0, $wait);
    }
}
