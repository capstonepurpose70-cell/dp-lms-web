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