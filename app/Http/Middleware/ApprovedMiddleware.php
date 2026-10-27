<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApprovedMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->status !== 'approved') {
            // Mobile (Sanctum): dati walang tsek sa API — gumagana pa ang token ng na-reject
            // o na-ban. Buburahin ang token (hindi ang session cookie) para mag-login ulit.
            if ($request->is('api/*')) {
                $token = $user->currentAccessToken();
                if ($token instanceof PersonalAccessToken) {
                    $token->delete();
                }
                return response()->json([
                    'message' => 'Your account is not active. Please contact the school.',
                ], 403);
            }

            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is pending approval by the administrator.');
        }
        return $next($request);
    }
}
