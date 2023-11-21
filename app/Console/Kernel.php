<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // \App\Console\Commands\App\AutoLoginReminder::class,
        // \App\Console\Commands\App\SendTrialCode::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('inspire')->hourly();

        $schedule
            ->command('app:send-trial-code', ['initiator' => 'system'])
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'))
            ->withoutOverlapping();

        $schedule
            ->command('app:auto-login-reminder', ['initiator' => 'system'])
            ->daily()
            ->appendOutputTo(storage_path('logs/schedule/auto-login-reminder.log'))
            ->withoutOverlapping();
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
