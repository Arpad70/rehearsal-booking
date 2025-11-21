<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule device health checks every minute
Schedule::command('devices:health-check')
    ->everyMinute()
    ->name('device-health-check')
    ->withoutOverlapping()
    ->runInBackground();
