<?php

namespace App\Services\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SchedulerService
{
    /**
     * Build a normalized list of scheduler jobs from config/classer.php.
     */
    public function listJobs(): Collection
    {
        return collect(config('classer.scheduler', []))
            ->map(fn (array $job, string $name): array => $this->normalizeJob($name, $job))
            ->values();
    }

    /**
     * Trigger the Laravel scheduler once and capture its output.
     *
     * @return array{exit_code:int, output:string}
     */
    public function triggerAllJobs(): array
    {
        $exitCode = Artisan::call('schedule:run', [
            '--verbose' => true,
        ]);

        return [
            'exit_code' => $exitCode,
            'output' => trim((string) Artisan::output()),
        ];
    }

    /**
     * Trigger a single scheduler job by key.
     *
     * @return array{exit_code:int, output:string}
     */
    public function triggerJob(string $name): array
    {
        $job = config('classer.scheduler.'.$name);

        if (! is_array($job)) {
            return [
                'exit_code' => 1,
                'output' => 'Scheduler job not found.',
            ];
        }

        if (isset($job['artisan']) && is_array($job['artisan'])) {
            $exitCode = Artisan::call(
                $job['artisan']['command'] ?? '',
                $job['artisan']['parameters'] ?? []
            );

            return [
                'exit_code' => $exitCode,
                'output' => trim((string) Artisan::output()),
            ];
        }

        return [
            'exit_code' => 1,
            'output' => 'Scheduler job cannot be executed directly.',
        ];
    }

    /**
     * Normalize a single scheduler config entry.
     *
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    private function normalizeJob(string $name, array $job): array
    {
        $artisan = $job['artisan'] ?? [];
        $parameters = $artisan['parameters'] ?? [];
        $mode = isset($job['artisan']) ? 'artisan' : 'command';
        $nextRunAt = $this->resolveNextRunAt((string) ($job['expression'] ?? ''));

        return [
            'key' => $name,
            'label' => Str::headline($name),
            'mode' => $mode,
            'expression' => $job['expression'] ?? '-',
            'next_run_at' => $nextRunAt,
            'next_run_at_iso' => $nextRunAt?->toIso8601String(),
            'next_run_at_label' => $nextRunAt?->format('d M Y, H:i'),
            'command' => $artisan['command'] ?? ($job['command'] ?? '-'),
            'connection' => $parameters['connection'] ?? null,
            'queue' => $parameters['--queue'] ?? null,
            'stop_when_empty' => (bool) ($parameters['--stop-when-empty'] ?? false),
            'sleep' => $parameters['--sleep'] ?? null,
            'tries' => $parameters['--tries'] ?? null,
            'timeout' => $parameters['--timeout'] ?? null,
            'without_overlapping' => $job['withoutOverlapping'] ?? null,
            'background' => (bool) ($job['background'] ?? true),
            'output' => $job['output'] ?? null,
        ];
    }

    private function resolveNextRunAt(string $expression): ?Carbon
    {
        if ($expression === '') {
            return null;
        }

        if (class_exists(\Cron\CronExpression::class)) {
            try {
                $nextRunDate = \Cron\CronExpression::factory($expression)->getNextRunDate(
                    now()->toDateTimeImmutable(),
                    0,
                    true,
                );

                return Carbon::instance($nextRunDate);
            } catch (\Throwable) {
                // Fall back to the lightweight matcher below.
            }
        }

        return $this->resolveNextRunAtFallback($expression);
    }

    private function resolveNextRunAtFallback(string $expression): ?Carbon
    {
        $parts = preg_split('/\s+/', trim($expression));

        if (! is_array($parts) || count($parts) !== 5) {
            return null;
        }

        $start = now()->startOfMinute();

        for ($offset = 1; $offset <= 525600; $offset++) {
            $candidate = $start->copy()->addMinutes($offset);

            if ($this->cronExpressionMatches($candidate, $parts)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $parts
     */
    private function cronExpressionMatches(Carbon $candidate, array $parts): bool
    {
        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        $minuteMatches = $this->cronFieldMatches((int) $candidate->minute, $minute, 0, 59);
        $hourMatches = $this->cronFieldMatches((int) $candidate->hour, $hour, 0, 23);
        $monthMatches = $this->cronFieldMatches((int) $candidate->month, $month, 1, 12);

        $dayOfMonthMatches = $this->cronFieldMatches((int) $candidate->day, $dayOfMonth, 1, 31);
        $dayOfWeekMatches = $this->cronFieldMatches((int) $candidate->dayOfWeek, $dayOfWeek, 0, 7);

        $dayOfMonthWildcard = trim($dayOfMonth) === '*';
        $dayOfWeekWildcard = trim($dayOfWeek) === '*';

        $dayMatches = $dayOfMonthWildcard || $dayOfWeekWildcard
            ? $dayOfMonthMatches && $dayOfWeekMatches
            : $dayOfMonthMatches || $dayOfWeekMatches;

        return $minuteMatches && $hourMatches && $monthMatches && $dayMatches;
    }

    private function cronFieldMatches(int $value, string $field, int $min, int $max): bool
    {
        $field = trim($field);

        if ($field === '*') {
            return true;
        }

        foreach (explode(',', $field) as $segment) {
            $segment = trim($segment);

            if ($segment === '') {
                continue;
            }

            if ($this->cronSegmentMatches($value, $segment, $min, $max)) {
                return true;
            }
        }

        return false;
    }

    private function cronSegmentMatches(int $value, string $segment, int $min, int $max): bool
    {
        if (str_contains($segment, '/')) {
            [$range, $step] = array_pad(explode('/', $segment, 2), 2, '*');
            $step = max(1, (int) $step);

            if ($range === '*' || $range === '') {
                return (($value - $min) % $step) === 0;
            }

            if (! str_contains($range, '-')) {
                return $value === (int) $range;
            }

            [$rangeStart, $rangeEnd] = array_map('intval', explode('-', $range, 2));

            return $value >= $rangeStart && $value <= $rangeEnd && (($value - $rangeStart) % $step) === 0;
        }

        if (str_contains($segment, '-')) {
            [$start, $end] = array_map('intval', explode('-', $segment, 2));

            return $value >= $start && $value <= $end;
        }

        if ($segment === '7' && $value === 0) {
            return true;
        }

        return $value === (int) $segment;
    }
}