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
        collect(config('classer.scheduler'))->each(function ($job, $name) use ($schedule) {
            $event = $schedule->command($job['command'])
                ->cron($job['expression'])
                ->withoutOverlapping($job['withoutOverlapping']); // prevents a new run if previous <30 min old

            if (! empty($job['output'])) {
                $event->appendOutputTo(storage_path('logs/'.$job['output']));
            }

            if (($job['background'] ?? true) === true) {
                $event->runInBackground();
            }
        });
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
