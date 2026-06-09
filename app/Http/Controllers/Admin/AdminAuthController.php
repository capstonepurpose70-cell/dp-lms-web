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

    // ── 1. Rate limit check MUNA ──
    $key = 'admin-login:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        AuditLogService::log('Admin login blocked — rate limit', 'Auth', $request->email);
        return back()->with('error', 'Too many attempts. Please try again in 30 minutes.');
    }

    // ── 2. Tapos na query at password check ──
    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        RateLimiter::hit($key, 45);
        AuditLogService::log('Failed admin login', 'Auth', $request->email);
        return back()->with('error', 'Invalid administrator credentials.')->withInput();
    }

    // ── 3. Success ──
    Auth::guard('admin')->login($admin);
    RateLimiter::clear($key);

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