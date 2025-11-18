<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ArchiveAccessLogsJob;

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

        // Optional: you can also add other scheduled tasks here
        // $schedule->command('inspire')->hourly();
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
