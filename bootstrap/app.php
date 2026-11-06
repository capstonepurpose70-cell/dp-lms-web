<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',  
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'        => \App\Http\Middleware\RoleMiddleware::class,
        'approved'    => \App\Http\Middleware\ApprovedMiddleware::class,
        'session.otp' => \App\Http\Middleware\SessionOtpMiddleware::class,
        'force.password' => \App\Http\Middleware\ForcePasswordChange::class,
        'terms'       => \App\Http\Middleware\EnsureTermsAccepted::class,
    ]);

    // Railway's proxy is the only way in. Without this every visitor shares the proxy's address,
    // so one person hitting a per-IP limit would lock out the whole school. Forwarded host headers
    // stay untrusted, so a visitor cannot choose the host of the links the system makes.
    $middleware->trustProxies(
        at: '*',
        headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
    );
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

    // Prepended so it wraps everything: prioritised middleware (auth, bindings) is sorted ahead of
    // appended entries, and a guest redirect from auth would otherwise skip it.
    $middleware->web(prepend: [
        \App\Http\Middleware\NoStorePages::class,
    ]);

    $middleware->validateCsrfTokens(except: [
        'api/*',
    ]);

    // Logged-out visitors: admin pages → admin login (the student/teacher /login
    // cannot sign in an admin). API requests still get 401 JSON.
    $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) =>
        $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();