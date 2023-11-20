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
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'))
            ->withoutOverlapping()
            ->runInBackground()
            ->onOneServer()
            // ->when(function () {
            //     return config('app.env') === 'local';
            // })
            // ->skip(function () {
            //     return config('app.env') === 'production';
            // })
            ->onSuccess(function () {
                echo 'success';
            })
            ->onFailure(function () {
                echo 'failure';
            })
            ->after(function () {
                echo 'after';
            })
            ->before(function () {
                echo 'before';
            });

        $schedule
            ->command('app:auto-login-reminder')
            ->daily()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'))
            ->withoutOverlapping()
            ->runInBackground()
            ->onOneServer();
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
