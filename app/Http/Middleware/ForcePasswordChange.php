<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (
            $user &&
            $user->must_change_password &&
            !$request->routeIs('password.change') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('password.change')
                ->with('info', 'Please change your password before continuing.');
        }

        return $next($request);
    }
}