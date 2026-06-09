<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS sa production (Railway behind proxy)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

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