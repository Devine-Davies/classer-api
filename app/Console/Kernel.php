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
         * Send admin analytics report
         */
        $schedule
            ->command('app:send-admin-analytics-report', ['initiator' => 'system'])
            ->weeklyOn(5, '12:00')
            ->appendOutputTo(storage_path('logs/send-admin-analytics-report.log'))
            ->withoutOverlapping();

        /**
         * Send Verification Email
         */
        $schedule
            ->command('app:send-verfication-email', ['initiator' => 'system'])
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/send-verfication-email.log'))
            ->withoutOverlapping();

        /**
         * Auto login reminder
         */
        $schedule
            ->command('app:auto-login-reminder', ['initiator' => 'system'])
            ->daily()
            ->appendOutputTo(storage_path('logs/auto-login-reminder.log'))
            ->withoutOverlapping();

        // /**
        //  * app:delete-s3-file
        //  */
        // $schedule
        //     ->command('app:user-delete-s3-file', ['initiator' => 'system'])
        //     ->everyMinute()
        //     ->appendOutputTo(storage_path('logs/delete-s3-file.log'))
        //     ->withoutOverlapping();
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
