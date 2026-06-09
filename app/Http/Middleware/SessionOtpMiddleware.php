<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionOtpMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('login')
                ->with('error', 'Please log in first.');
        }
        return $next($request);
    }
}