<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApprovedMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check() && auth()->user()->status !== 'approved') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is pending approval by the administrator.');
        }
        return $next($request);
    }
}