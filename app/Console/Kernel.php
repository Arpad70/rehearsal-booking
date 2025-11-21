<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ArchiveAccessLogsJob;
use App\Jobs\CollectPowerMonitoringDataJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Archive access logs older than 1 year - run daily at 2 AM
        $schedule->job(new ArchiveAccessLogsJob())
            ->dailyAt('02:00')
            ->name('archive-access-logs')
            ->withoutOverlapping();

        // Collect power monitoring data from Shelly devices every 5 minutes
        $schedule->job(new CollectPowerMonitoringDataJob())
            ->everyFiveMinutes()
            ->name('collect-power-data')
            ->withoutOverlapping();

        // Health check all IoT devices every minute
        $schedule->command('devices:health-check')
            ->everyMinute()
            ->name('device-health-check')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
