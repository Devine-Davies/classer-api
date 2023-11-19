<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inspire')->hourly();

        $schedule
            ->command('app:send-trial-code')
            ->everySecond()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'));
            // ->withoutOverlapping()
            // ->runInBackground()
            // ->onOneServer();

        $schedule
            ->command('app:auto-login-reminder')
            ->everySecond()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'));
            // ->withoutOverlapping()
            // ->runInBackground()
            // ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
