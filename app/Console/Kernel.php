<?php

namespace App\Console;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        if (! config('classer.scheduler_enabled', true)) {
            return;
        }

        collect(config('classer.scheduler'))->each(function ($job, $name) use ($schedule) {
            $event = $this->makeScheduledEvent($schedule, $job);

            $this->configureScheduledEvent($event, $job, $name);
        });
    }

    /**
     * Create a scheduled event for the given job.
     *
     * @param  Schedule  $schedule
     * @param  array<string, mixed>  $job
     * @return Event|CallbackEvent
     */
    private function makeScheduledEvent(Schedule $schedule, array $job): Event|CallbackEvent
    {
        if (isset($job['artisan'])) {
            return $schedule->call(fn () => $this->runInlineArtisanJob($job))
                ->cron($job['expression']);
        }

        return $schedule->command($job['command'])
            ->cron($job['expression']);
    }

    /**
     * Configure a scheduled event.
     *
     * @param  Event|CallbackEvent  $event
     * @param  array<string, mixed>  $job
     * @param  int|string  $name
     */
    private function configureScheduledEvent(Event|CallbackEvent $event, array $job, int|string $name): void
    {
        if (is_string($name) && $name !== '') {
            $event->name('scheduler:'.$name);
        }

        if (! empty($job['withoutOverlapping'])) {
            $event->withoutOverlapping($job['withoutOverlapping']);
        }

        if (! empty($job['output']) && ! isset($job['artisan'])) {
            $event->appendOutputTo($this->schedulerLogPath($job['output']));
        }

        if (($job['background'] ?? true) === true) {
            $event->runInBackground();
        }
    }

    /**
     * Run an inline Artisan job.
     *
     * @param  array<string, mixed>  $job
     */
    private function runInlineArtisanJob(array $job): void
    {
        $exitCode = Artisan::call(
            $job['artisan']['command'],
            $job['artisan']['parameters'] ?? []
        );

        if (! empty($job['output'])) {
            $this->appendInlineArtisanOutput($job['output'], $job['artisan']['command'], $exitCode);
        }
    }

    /**
     * Append the output of an inline Artisan job to the specified log file.
     *
     * @param  string  $outputFile
     * @param  string  $command
     * @param  int  $exitCode
     */
    private function appendInlineArtisanOutput(string $outputFile, string $command, int $exitCode): void
    {
        $output = trim(Artisan::output());

        if ($exitCode === 0 && $output === '') {
            return;
        }

        $logLines = [
            now()->toDateTimeString().' ['.$command.'] exit='.$exitCode,
        ];

        if ($output !== '') {
            $logLines[] = $output;
        }

        file_put_contents(
            $this->schedulerLogPath($outputFile),
            implode(PHP_EOL, $logLines).PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Get the full path to the scheduler log file.
     *
     * @param  string  $outputFile
     * @return string
     */
    private function schedulerLogPath(string $outputFile): string
    {
        return storage_path('logs/'.$outputFile);
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
