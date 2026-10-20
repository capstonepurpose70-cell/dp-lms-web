<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function show()
    {
        return view('admin.login');
    }

public function submit(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // ── 1. Rate limit check MUNA (kada IP at kada email) ──
    // Dati: hit($key, 45) = 45 SEGUNDO lang ang lock kahit "30 minutes" ang
    // mensahe → 5 hula ng admin password kada 45 segundo.
    $key      = 'admin-login:' . $request->ip();
    $emailKey = 'admin-login-email:' . mb_strtolower((string) $request->email);

    if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($emailKey, 10)) {
        AuditLogService::log('Admin login blocked — rate limit', 'Auth', $request->email);
        return back()->with('error', 'Too many attempts. Please try again in 30 minutes.');
    }

    // ── 2. Tapos na query at password check ──
    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        RateLimiter::hit($key, 1800);
        RateLimiter::hit($emailKey, 1800);
        AuditLogService::log('Failed admin login', 'Auth', $request->email);
        return back()->with('error', 'Invalid administrator credentials.')->withInput();
    }

    // ── 3. Success ──
    Auth::guard('admin')->login($admin);
    $request->session()->regenerate();   // panlaban sa session fixation
    RateLimiter::clear($key);
    RateLimiter::clear($emailKey);

return redirect()->route('admin.dashboard')
    ->with('login_success', true);
}


    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}