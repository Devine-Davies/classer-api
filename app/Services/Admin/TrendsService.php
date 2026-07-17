<?php

namespace App\Services\Admin;

use App\Enums\RecorderCodes;
use App\Models\CloudShare;
use App\Models\RecorderModel;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TrendsService
{
    /**
     * Build trends payload for the admin trends page.
     */
    public function build(Request $request): array
    {
        $domains = $this->domainOptions();

        $domain = strtolower(trim((string) $request->query('domain', 'users')));
        if (! array_key_exists($domain, $domains)) {
            $domain = 'users';
        }

        $interval = strtolower(trim((string) $request->query('interval', 'daily')));
        if (! in_array($interval, ['hourly', 'daily', 'weekly', 'monthly', 'yearly'], true)) {
            $interval = 'daily';
        }

        $timezone = (string) config('app.timezone', 'UTC');

        $range = $this->resolveRange(
            startDateInput: (string) $request->query('start_date', ''),
            endDateInput: (string) $request->query('end_date', ''),
            interval: $interval,
            timezone: $timezone
        );

        $meta = [
            'interval' => $interval,
            'timezone' => $timezone,
            'start_date' => $range['start']->toDateString(),
            'end_date' => $range['end']->toDateString(),
            'start_utc' => $range['start']->copy()->utc(),
            'end_utc' => $range['end']->copy()->utc(),
        ];

        $series = $this->domainSeries($domain, $meta);
        $rowsCollection = $this->seriesRows($series, $meta);

        $perPage = max(10, min((int) $request->query('limit', 30), 200));
        $currentPage = max((int) $request->query('page', 1), 1);
        $total = $rowsCollection->count();

        $paginated = new LengthAwarePaginator(
            $rowsCollection->forPage($currentPage, $perPage)->values()->all(),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        return [
            'domainOptions' => collect($domains)->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
            'activeDomain' => $domain,
            'series' => $series,
            'rows' => collect($paginated->items())->map(fn (array $row) => (object) $row),
            'filters' => [
                'domain' => $domain,
                'interval' => $interval,
                'start_date' => $meta['start_date'],
                'end_date' => $meta['end_date'],
                'limit' => $perPage,
            ],
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ];
    }

    /**
     * Build per-domain display labels.
     *
     * @return array<string, string>
     */
    private function domainOptions(): array
    {
        return [
            'users' => 'Users',
            'plans' => 'Plans',
            'cloudshares' => 'Cloud Share',
            'logins' => 'Logins',
        ];
    }

    /**
     * Resolve date range with sane defaults per interval.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    private function resolveRange(string $startDateInput, string $endDateInput, string $interval, string $timezone): array
    {
        $now = now($timezone);

        $defaultStart = match ($interval) {
            'hourly' => $now->copy()->subDays(3)->startOfDay(),
            'daily' => $now->copy()->subDays(30)->startOfDay(),
            'weekly' => $now->copy()->subWeeks(26)->startOfWeek(),
            'monthly' => $now->copy()->subMonths(12)->startOfMonth(),
            'yearly' => $now->copy()->subYears(5)->startOfYear(),
            default => $now->copy()->subDays(30)->startOfDay(),
        };

        $start = $defaultStart;
        $end = $now->copy()->endOfDay();

        if ($startDateInput !== '') {
            try {
                $start = Carbon::parse($startDateInput, $timezone)->startOfDay();
            } catch (\Throwable) {
            }
        }

        if ($endDateInput !== '') {
            try {
                $end = Carbon::parse($endDateInput, $timezone)->endOfDay();
            } catch (\Throwable) {
            }
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        if ($this->estimateBucketCount($start, $end, $interval) > 500) {
            $start = $defaultStart;
            $end = $now->copy()->endOfDay();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Build domain-specific trend series.
     *
     * @return array<int, array<string, mixed>>
     */
    private function domainSeries(string $domain, array $meta): array
    {
        return match ($domain) {
            'users' => $this->usersSeries($meta),
            'plans' => $this->plansSeries($meta),
            'cloudshares' => $this->cloudSharesSeries($meta),
            'logins' => $this->loginsSeries($meta),
            default => $this->usersSeries($meta),
        };
    }

    /**
     * Build users trend series.
     */
    private function usersSeries(array $meta): array
    {
        $rows = User::query()
            ->whereBetween('created_at', [$meta['start_utc'], $meta['end_utc']])
            ->get(['created_at']);

        return [
            $this->bucketCountSeries($rows, 'created_at', $meta, 'newUsers', 'New Users'),
        ];
    }

    /**
     * Build plans trend series.
     */
    private function plansSeries(array $meta): array
    {
        $rows = UserSubscription::query()
            ->whereBetween('created_at', [$meta['start_utc'], $meta['end_utc']])
            ->get(['created_at', 'status']);

        $activeRows = $rows->filter(fn ($row) => strtolower((string) $row->status) === 'active')->values();
        $canceledRows = $rows->filter(fn ($row) => strtolower((string) $row->status) === 'canceled')->values();

        return [
            $this->bucketCountSeries($rows, 'created_at', $meta, 'newPlans', 'New Plans'),
            $this->bucketCountSeries($activeRows, 'created_at', $meta, 'activePlans', 'Active Plans'),
            $this->bucketCountSeries($canceledRows, 'created_at', $meta, 'canceledPlans', 'Canceled Plans'),
        ];
    }

    /**
     * Build cloud shares trend series.
     */
    private function cloudSharesSeries(array $meta): array
    {
        $rows = CloudShare::withTrashed()
            ->where(function ($query) use ($meta) {
                $query->whereBetween('created_at', [$meta['start_utc'], $meta['end_utc']])
                    ->orWhereBetween('deleted_at', [$meta['start_utc'], $meta['end_utc']]);
            })
            ->get(['created_at', 'deleted_at', 'size']);

        $deletedRows = $rows->filter(fn ($row) => ! empty($row->deleted_at))->values();

        return [
            $this->bucketCountSeries($rows, 'created_at', $meta, 'createdShares', 'Created Shares'),
            $this->bucketSumSeries($rows, 'created_at', 'size', $meta, 'createdSize', 'Created Size (Bytes)'),
            $this->bucketCountSeries($deletedRows, 'deleted_at', $meta, 'deletedShares', 'Deleted Shares'),
            $this->bucketSumSeries($deletedRows, 'deleted_at', 'size', $meta, 'deletedSize', 'Deleted Size (Bytes)'),
        ];
    }

    /**
     * Build logins trend series.
     */
    private function loginsSeries(array $meta): array
    {
        $rows = RecorderModel::query()
            ->whereIn('code', [
                RecorderCodes::USER_LOGIN->value,
                RecorderCodes::USER_AUTO_LOGIN->value,
            ])
            ->whereBetween('created_at', [$meta['start_utc'], $meta['end_utc']])
            ->get(['created_at']);

        return [
            $this->bucketCountSeries($rows, 'created_at', $meta, 'loginEvents', 'Login Events'),
        ];
    }

    /**
     * Bucket rows into count-based series.
     */
    private function bucketCountSeries(Collection $rows, string $timestampField, array $meta, string $key, string $label): array
    {
        $points = $this->emptyPoints($meta);

        foreach ($rows as $row) {
            $stamp = $row->{$timestampField};
            if (! $stamp) {
                continue;
            }

            $bucket = $this->bucketKey(Carbon::parse($stamp)->tz($meta['timezone']), $meta['interval']);
            if (! array_key_exists($bucket, $points)) {
                continue;
            }

            $points[$bucket] += 1;
        }

        return $this->toSeries($key, $label, $points);
    }

    /**
     * Bucket rows into sum-based series.
     */
    private function bucketSumSeries(Collection $rows, string $timestampField, string $valueField, array $meta, string $key, string $label): array
    {
        $points = $this->emptyPoints($meta);

        foreach ($rows as $row) {
            $stamp = $row->{$timestampField};
            if (! $stamp) {
                continue;
            }

            $bucket = $this->bucketKey(Carbon::parse($stamp)->tz($meta['timezone']), $meta['interval']);
            if (! array_key_exists($bucket, $points)) {
                continue;
            }

            $points[$bucket] += (int) ($row->{$valueField} ?? 0);
        }

        return $this->toSeries($key, $label, $points);
    }

    /**
     * Convert series points into table rows with per-series totals.
     */
    private function seriesRows(array &$series, array $meta): Collection
    {
        $rowsByBucket = [];

        foreach ($series as $index => $entry) {
            $total = 0;

            foreach ($entry['points'] as $point) {
                $bucket = (string) ($point['x'] ?? '');
                $value = (int) ($point['y'] ?? 0);
                $total += $value;

                if (! isset($rowsByBucket[$bucket])) {
                    $rowsByBucket[$bucket] = [
                        'bucket' => $bucket,
                        'label' => $this->formatBucketLabel($bucket, $meta['interval'], $meta['timezone']),
                        'values' => [],
                    ];
                }

                $rowsByBucket[$bucket]['values'][$entry['key']] = $value;
            }

            $series[$index]['total'] = $total;
        }

        $seriesKeys = collect($series)->pluck('key')->all();

        return collect($rowsByBucket)
            ->sortByDesc('bucket')
            ->map(function (array $row) use ($seriesKeys): array {
                foreach ($seriesKeys as $seriesKey) {
                    $row['values'][$seriesKey] = (int) ($row['values'][$seriesKey] ?? 0);
                }

                return $row;
            })
            ->values();
    }

    /**
     * Create zero-filled bucket map.
     */
    private function emptyPoints(array $meta): array
    {
        $start = Carbon::parse($meta['start_utc'])->tz($meta['timezone']);
        $end = Carbon::parse($meta['end_utc'])->tz($meta['timezone']);

        $points = [];
        $cursor = $this->normalizeToBucketStart($start, $meta['interval']);
        $endBucket = $this->normalizeToBucketStart($end, $meta['interval']);

        while ($cursor->lte($endBucket)) {
            $points[$this->bucketKey($cursor, $meta['interval'])] = 0;
            $cursor = $this->stepBucket($cursor, $meta['interval']);
        }

        return $points;
    }

    /**
     * Build consistent bucket key.
     */
    private function bucketKey(Carbon $date, string $interval): string
    {
        return $this->normalizeToBucketStart($date, $interval)->toIso8601String();
    }

    /**
     * Normalize date to bucket start.
     */
    private function normalizeToBucketStart(Carbon $date, string $interval): Carbon
    {
        return match ($interval) {
            'hourly' => $date->copy()->startOfHour(),
            'daily' => $date->copy()->startOfDay(),
            'weekly' => $date->copy()->startOfWeek(),
            'monthly' => $date->copy()->startOfMonth(),
            'yearly' => $date->copy()->startOfYear(),
            default => $date->copy()->startOfDay(),
        };
    }

    /**
     * Move to the next bucket.
     */
    private function stepBucket(Carbon $date, string $interval): Carbon
    {
        return match ($interval) {
            'hourly' => $date->copy()->addHour(),
            'daily' => $date->copy()->addDay(),
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonth(),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addDay(),
        };
    }

    /**
     * Estimate bucket count for the selected range.
     */
    private function estimateBucketCount(Carbon $startDate, Carbon $endDate, string $interval): int
    {
        return match ($interval) {
            'hourly' => (int) $startDate->diffInHours($endDate) + 1,
            'daily' => (int) $startDate->diffInDays($endDate) + 1,
            'weekly' => (int) $startDate->diffInWeeks($endDate) + 1,
            'monthly' => (int) $startDate->diffInMonths($endDate) + 1,
            'yearly' => (int) $startDate->diffInYears($endDate) + 1,
            default => (int) $startDate->diffInDays($endDate) + 1,
        };
    }

    /**
     * Convert associative points into series format.
     */
    private function toSeries(string $key, string $label, array $points): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'points' => array_map(
                fn ($x, $y) => ['x' => $x, 'y' => $y],
                array_keys($points),
                array_values($points)
            ),
        ];
    }

    /**
     * Format a bucket key for table display.
     */
    private function formatBucketLabel(string $bucketKey, string $interval, string $timezone): string
    {
        $date = Carbon::parse($bucketKey)->tz($timezone);

        return match ($interval) {
            'hourly' => $date->format('d M Y H:i'),
            'daily' => $date->format('d M Y'),
            'weekly' => $date->startOfWeek()->format('d M Y').' - '.$date->endOfWeek()->format('d M Y'),
            'monthly' => $date->format('M Y'),
            'yearly' => $date->format('Y'),
            default => $date->format('d M Y'),
        };
    }
}
