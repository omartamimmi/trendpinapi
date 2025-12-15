<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Clean up old activity logs daily at 2 AM
Schedule::command('logs:cleanup --days=30')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
