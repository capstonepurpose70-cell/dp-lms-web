<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Class attendance: when a session's window ends, unmarked students become ABSENT and
// parents are notified. Pages also do this lazily on every read, so attendance stays
// correct even if the scheduler is not running (Railway: run `php artisan schedule:work`).
Artisan::command('attendance:finalize', function (App\Services\ClassAttendanceService $service) {
    $n = $service->finalizeAllDue();
    $this->info("Finalized {$n} class attendance session(s).");
})->purpose('Close ended class attendance sessions and mark absents');

Schedule::command('attendance:finalize')->everyMinute()->withoutOverlapping();
