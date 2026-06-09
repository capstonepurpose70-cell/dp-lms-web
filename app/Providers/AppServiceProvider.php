<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\EnrollmentRequest;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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