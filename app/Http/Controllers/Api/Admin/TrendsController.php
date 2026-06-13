<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\RecorderCodes;
use App\Http\Controllers\Controller;
use App\Models\CloudShare;
use App\Models\RecorderModel;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class TrendsController extends Controller
{
    /**
     * Return user registration trend data for the selected range and interval.
     *
     * @param  Request  $request  Request containing trend query params.
     * @return JsonResponse Trend response payload or validation error.
     */
    public function users(Request $request): JsonResponse
    {
        $resolved = $this->resolveTrendQuery($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$meta, $startUtc, $endUtc] = $resolved;

        $rows = User::query()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->get(['created_at']);

        $series = $this->bucketCountSeries($rows, 'created_at', $meta, 'newUsers', 'New Users');

        return $this->trendResponse($meta, [$series]);
    }

    /**
     * Return subscription trend data for the selected range and interval.
     *
     * @param  Request  $request  Request containing trend query params.
     * @return JsonResponse Trend response payload or validation error.
     */
    public function plans(Request $request): JsonResponse
    {
        $resolved = $this->resolveTrendQuery($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$meta, $startUtc, $endUtc] = $resolved;

        $rows = UserSubscription::query()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->get(['created_at', 'status']);

        $createdSeries = $this->bucketCountSeries($rows, 'created_at', $meta, 'newPlans', 'New Plans');

        $activeRows = $rows->filter(fn ($row) => strtolower((string) $row->status) === 'active')->values();
        $activeSeries = $this->bucketCountSeries($activeRows, 'created_at', $meta, 'activePlans', 'Active Plans');

        $canceledRows = $rows->filter(fn ($row) => strtolower((string) $row->status) === 'canceled')->values();
        $canceledSeries = $this->bucketCountSeries($canceledRows, 'created_at', $meta, 'canceledPlans', 'Canceled Plans');

        return $this->trendResponse($meta, [$createdSeries, $activeSeries, $canceledSeries]);
    }

    /**
     * Return cloud share trend data for the selected range and interval.
     *
     * @param  Request  $request  Request containing trend query params.
     * @return JsonResponse Trend response payload or validation error.
     */
    public function cloudShares(Request $request): JsonResponse
    {
        $resolved = $this->resolveTrendQuery($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$meta, $startUtc, $endUtc] = $resolved;

        $rows = CloudShare::withTrashed()
            ->where(function ($query) use ($startUtc, $endUtc) {
                $query->whereBetween('created_at', [$startUtc, $endUtc])
                    ->orWhereBetween('deleted_at', [$startUtc, $endUtc]);
            })
            ->get(['created_at', 'deleted_at', 'size']);

        $createdCount = $this->bucketCountSeries($rows, 'created_at', $meta, 'createdShares', 'Created Shares');
        $createdSize = $this->bucketSumSeries($rows, 'created_at', 'size', $meta, 'createdSize', 'Created Size (Bytes)');

        $deletedRows = $rows->filter(fn ($row) => ! empty($row->deleted_at))->values();
        $deletedCount = $this->bucketCountSeries($deletedRows, 'deleted_at', $meta, 'deletedShares', 'Deleted Shares');
        $deletedSize = $this->bucketSumSeries($deletedRows, 'deleted_at', 'size', $meta, 'deletedSize', 'Deleted Size (Bytes)');

        return $this->trendResponse($meta, [$createdCount, $createdSize, $deletedCount, $deletedSize]);
    }

    /**
     * Return login trend data for the selected range and interval.
     *
     * @param  Request  $request  Request containing trend query params.
     * @return JsonResponse Trend response payload or validation error.
     */
    public function logins(Request $request): JsonResponse
    {
        $resolved = $this->resolveTrendQuery($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$meta, $startUtc, $endUtc] = $resolved;

        $rows = RecorderModel::query()
            ->whereIn('code', [
                RecorderCodes::USER_LOGIN->value,
                RecorderCodes::USER_AUTO_LOGIN->value,
            ])
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->get(['created_at']);

        $series = $this->bucketCountSeries($rows, 'created_at', $meta, 'loginEvents', 'Login Events');

        return $this->trendResponse($meta, [$series]);
    }

    /**
     * Validate trend query parameters and resolve UTC range boundaries.
     *
     * @param  Request  $request  Request with startDate, endDate, interval, and timezone params.
     * @return array|JsonResponse Resolved meta/start/end tuple or validation error response.
     */
    protected function resolveTrendQuery(Request $request): array|JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'interval' => ['nullable', 'in:hourly,daily,weekly,monthly,yearly'],
            'timezone' => ['nullable', 'timezone'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid trend query parameters.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $interval = (string) ($request->query('interval', 'daily'));
        $timezone = (string) ($request->query('timezone', 'UTC'));

        $nowTz = now($timezone);
        $defaultStart = match ($interval) {
            'hourly' => $nowTz->copy()->subDays(3)->startOfDay(),
            'daily' => $nowTz->copy()->subDays(30)->startOfDay(),
            'weekly' => $nowTz->copy()->subWeeks(26)->startOfWeek(),
            'monthly' => $nowTz->copy()->subMonths(12)->startOfMonth(),
            'yearly' => $nowTz->copy()->subYears(5)->startOfYear(),
            default => $nowTz->copy()->subDays(30)->startOfDay(),
        };

        $startDate = $request->query('startDate')
            ? Carbon::parse((string) $request->query('startDate'), $timezone)->startOfDay()
            : $defaultStart;

        $endDate = $request->query('endDate')
            ? Carbon::parse((string) $request->query('endDate'), $timezone)->endOfDay()
            : $nowTz->copy()->endOfDay();

        if ($startDate->gt($endDate)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid trend query parameters.',
                'errors' => ['The startDate must be before or equal to endDate.'],
            ], 422);
        }

        $bucketCount = $this->estimateBucketCount($startDate, $endDate, $interval);
        if ($bucketCount > 500) {
            return response()->json([
                'status' => false,
                'message' => 'Date range too large for selected interval.',
                'errors' => ['Please shorten the range or use a larger interval.'],
            ], 422);
        }

        $meta = [
            'startDate' => $startDate->toIso8601String(),
            'endDate' => $endDate->toIso8601String(),
            'interval' => $interval,
            'timezone' => $timezone,
        ];

        return [$meta, $startDate->copy()->utc(), $endDate->copy()->utc()];
    }

    /**
     * Bucket rows into count-based trend points.
     *
     * @param  Collection  $rows  Collection of rows containing timestamp field.
     * @param  string  $timestampField  Row field used for bucket timestamp.
     * @param  array  $meta  Trend metadata (interval/timezone/range).
     * @param  string  $key  Series key identifier.
     * @param  string  $label  Human-friendly series label.
     * @return array Trend series payload.
     */
    protected function bucketCountSeries(Collection $rows, string $timestampField, array $meta, string $key, string $label): array
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
     * Bucket rows into sum-based trend points.
     *
     * @param  Collection  $rows  Collection of rows containing timestamp/value fields.
     * @param  string  $timestampField  Row field used for bucket timestamp.
     * @param  string  $valueField  Row field used for numeric summation.
     * @param  array  $meta  Trend metadata (interval/timezone/range).
     * @param  string  $key  Series key identifier.
     * @param  string  $label  Human-friendly series label.
     * @return array Trend series payload.
     */
    protected function bucketSumSeries(Collection $rows, string $timestampField, string $valueField, array $meta, string $key, string $label): array
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
     * Generate zero-filled buckets for the entire date range.
     *
     * @param  array  $meta  Trend metadata (interval/timezone/range).
     * @return array Associative array keyed by bucket timestamp.
     */
    protected function emptyPoints(array $meta): array
    {
        $start = Carbon::parse($meta['startDate'])->tz($meta['timezone']);
        $end = Carbon::parse($meta['endDate'])->tz($meta['timezone']);
        $interval = $meta['interval'];

        $points = [];
        $cursor = $this->normalizeToBucketStart($start, $interval);
        $endBucket = $this->normalizeToBucketStart($end, $interval);

        while ($cursor->lte($endBucket)) {
            $points[$this->bucketKey($cursor, $interval)] = 0;
            $cursor = $this->stepBucket($cursor, $interval);
        }

        return $points;
    }

    /**
     * Convert a timestamp to a normalized bucket key.
     *
     * @param  Carbon  $date  Date instance to normalize.
     * @param  string  $interval  Bucket interval.
     * @return string ISO timestamp key for the bucket.
     */
    protected function bucketKey(Carbon $date, string $interval): string
    {
        $bucketDate = $this->normalizeToBucketStart($date, $interval);

        return $bucketDate->toIso8601String();
    }

    /**
     * Normalize a date to the start of its bucket interval.
     *
     * @param  Carbon  $date  Date instance to normalize.
     * @param  string  $interval  Bucket interval.
     * @return Carbon Normalized date instance.
     */
    protected function normalizeToBucketStart(Carbon $date, string $interval): Carbon
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
     * Move a bucket cursor forward by one interval.
     *
     * @param  Carbon  $date  Current bucket date.
     * @param  string  $interval  Bucket interval.
     * @return Carbon Incremented bucket date.
     */
    protected function stepBucket(Carbon $date, string $interval): Carbon
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
     * Estimate how many buckets a trend query will generate.
     *
     * @param  Carbon  $startDate  Range start.
     * @param  Carbon  $endDate  Range end.
     * @param  string  $interval  Bucket interval.
     * @return int Estimated bucket count.
     */
    protected function estimateBucketCount(Carbon $startDate, Carbon $endDate, string $interval): int
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
     * Convert associative points into API series shape.
     *
     * @param  string  $key  Series key identifier.
     * @param  string  $label  Human-friendly series label.
     * @param  array  $points  Associative points array keyed by timestamp.
     * @return array Structured series payload.
     */
    protected function toSeries(string $key, string $label, array $points): array
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
     * Build a normalized trend API response payload.
     *
     * @param  array  $meta  Trend metadata for the query.
     * @param  array  $series  List of trend series.
     * @return JsonResponse JSON response for trend endpoints.
     */
    protected function trendResponse(array $meta, array $series): JsonResponse
    {
        return response()->json([
            'status' => true,
            'meta' => $meta,
            'series' => $series,
        ]);
    }
}
