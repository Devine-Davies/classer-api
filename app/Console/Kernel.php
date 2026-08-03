<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        collect(config('classer.scheduler'))->each(function ($job, $name) use ($schedule) {
            if (isset($job['artisan'])) {
                $event = $schedule->call(function () use ($job) {
                    $output = null;
                    $exitCode = Artisan::call(
                        $job['artisan']['command'],
                        $job['artisan']['parameters'] ?? []
                    );

                    if (! empty($job['output'])) {
                        $output = trim(Artisan::output());
                        $logLines = [
                            now()->toDateTimeString().' ['.$job['artisan']['command'].'] exit='.$exitCode,
                        ];

                        if ($output !== '') {
                            $logLines[] = $output;
                        }

                        file_put_contents(
                            storage_path('logs/'.$job['output']),
                            implode(PHP_EOL, $logLines).PHP_EOL,
                            FILE_APPEND
                        );
                    }
                })->cron($job['expression']);
            } else {
                $event = $schedule->command($job['command'])
                    ->cron($job['expression']);
            }

            if (! empty($job['withoutOverlapping'])) {
                $event->withoutOverlapping($job['withoutOverlapping']); // prevents a new run if previous <30 min old
            }

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
