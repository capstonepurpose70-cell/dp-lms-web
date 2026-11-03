<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Nobody uses the portal before agreeing to the Terms of Use and Privacy Policy. New accounts agree when
 * they activate; older accounts are sent to the review page once, on their next sign-in.
 */
class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->terms_accepted_at === null) {
            if ($request->expectsJson()) {
                abort(403, 'Please accept the Terms of Use and Privacy Policy first.');
            }

            return redirect()->guest(route('terms.accept'));
        }

        return $next($request);
    }
}
