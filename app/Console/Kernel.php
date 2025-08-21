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
        collect(config('classer.scheduler'))->each(function ($job) use ($schedule) {
            $schedule->command($job['command'])
                ->cron($job['expression'])
                ->withoutOverlapping(30) // prevents a new run if previous <30 min old
                ->onOneServer()          // if you have multiple web/queue nodes
                ->runInBackground();
        });
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