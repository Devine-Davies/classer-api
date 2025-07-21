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
        $schedule->command('queue:work --queue=mail --once')
            ->everyTenSeconds(); //dailyAt('00:00');
            // ->withoutOverlapping();

        $schedule->command('queue:work cloudshare --queue=verify --once')
            ->everyTenSeconds(); //everyMinute();
            // ->withoutOverlapping();

        $schedule->command('queue:work cloudshare --queue=expire --once')
            ->everyTwentySeconds(); //dailyAt('00:00');
            // ->withoutOverlapping();
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

// $schedule->command('queue:work cloudshare --queue=verify')
//     ->everySecond()
//     ->withoutOverlapping(); //everyMinute();

// /**
//  * Immediate Jobs
//  */
// $schedule
//     ->command('app:immediate', ['initiator' => 'system'])
//     ->everyMinute()
//     ->appendOutputTo(storage_path('logs/command-immediate.log'))
//     ->withoutOverlapping();

// /**
//  * Daily Jobs
//  */
// $schedule
//     ->command('app:daily', ['initiator' => 'system'])
//     ->daily()
//     ->appendOutputTo(storage_path('logs/command-daily.log'))
//     ->withoutOverlapping();

// /**
//  * Cloud Share Cleanup
//  */
// $schedule
//     ->command('app:cloud-share-cleanup', ['initiator' => 'system'])
//     // ->daily()
//     ->appendOutputTo(storage_path('logs/app-cloud-share-cleanup.log'))
//     ->withoutOverlapping()
//     ->onSuccess(function () {
//         // lets send an email or notification here if needed
//         // we should return the total number of files deleted
//     })
//     ->onSuccessWithOutput(function ($output) {
//         printf($output);
//     })
//     ->onFailure(function () {
//         // lets send an email or notification here if needed
//         // we should return the error message
//     });