<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\EnrollmentRequest;
use App\Mail\Transport\BrevoTransport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production so forms/links are not flagged "not secure"
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Register the Brevo HTTP-API mail transport (MAIL_MAILER=brevo)
        Mail::extend('brevo', function (array $config) {
            return new BrevoTransport($config['key'] ?? env('BREVO_API_KEY', ''));
        });

        // Auto-share pendingCount sa lahat ng faculty.* views
        View::composer('faculty.*', function ($view) {
            if (Auth::check()) {
                $view->with('pendingCount',
                    EnrollmentRequest::where('status', 'pending')->count()
                );
            }
        });
    }
}