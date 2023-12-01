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
        /**
         * send-amin-analytics-report
         */
        $schedule
            ->command('app:send-admin-analytics-report', ['initiator' => 'system'])
            ->weeklyOn(5, '12:00')
            ->appendOutputTo(storage_path('logs/send-admin-analytics-report.log'))
            ->withoutOverlapping();

        /**
         * send-trial-code
         */
        $schedule
            ->command('app:send-code', ['initiator' => 'system'])
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/send-trial-code.log'))
            ->withoutOverlapping();

        /**
         * auto-login-reminder
         */
        $schedule
            ->command('app:auto-login-reminder', ['initiator' => 'system'])
            ->daily()
            ->appendOutputTo(storage_path('logs/auto-login-reminder.log'))
            ->withoutOverlapping();

        // $schedule->command('inspire')->hourly();
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
