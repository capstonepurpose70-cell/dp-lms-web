<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Web pages are never kept by the browser (HTTP cache or back/forward cache).
 *
 * Without this, Back after logout showed the signed-in dashboard again, and the login page
 * came back with the typed LRN and password still in it — on a shared school computer the next
 * person could read them. With no-store the browser asks the server again, and the server
 * answers a signed-out visitor with the login page.
 *
 * Files and JSON keep their own headers.
 */
class NoStorePages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // A view gets its Content-Type only when it is sent, so "not set yet" means HTML too
        $type = (string) $response->headers->get('Content-Type', '');
        if ($type !== '' && !str_contains($type, 'text/html')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
