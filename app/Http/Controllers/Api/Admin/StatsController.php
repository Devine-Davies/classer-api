<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudShare;
use App\Models\RecorderModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class StatsController extends Controller
{
    /**
     * Return the total user count for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with the totalUsers metric.
     */
    public function totalUsers(Request $request): JsonResponse
    {
        $count = $this->resolveTotalUsers($request);
        if ($count instanceof JsonResponse) {
            return $count;
        }

        return $this->metricResponse('totalUsers', $count);
    }

    /**
     * Return the registration count for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with the registers metric.
     */
    public function registers(Request $request): JsonResponse
    {
        $count = $this->resolveRegisters($request);
        if ($count instanceof JsonResponse) {
            return $count;
        }

        return $this->metricResponse('registers', $count);
    }

    /**
     * Return the login event count for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with the logins metric.
     */
    public function logins(Request $request): JsonResponse
    {
        $count = $this->resolveLogins($request);
        if ($count instanceof JsonResponse) {
            return $count;
        }

        return $this->metricResponse('logins', $count);
    }

    /**
     * Return cloud share aggregate totals for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with cloud share total and size values.
     */
    public function cloudShares(Request $request): JsonResponse
    {
        $data = $this->resolveCloudShareTotals($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        return response()->json([
            'status' => true,
            'metric' => 'cloudShares',
            'data' => $data,
        ]);
    }

    /**
     * Return active cloud share aggregate totals for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with active cloud share total and size values.
     */
    public function cloudShareActive(Request $request): JsonResponse
    {
        $data = $this->resolveCloudShareActiveTotals($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        return response()->json([
            'status' => true,
            'metric' => 'cloudShareActive',
            'data' => $data,
        ]);
    }

    /**
     * Return deleted cloud share aggregate totals for an optional date range.
     *
     * @param Request $request Request containing optional startDate/endDate filters.
     * @return JsonResponse JSON payload with deleted cloud share total and size values.
     */
    public function cloudShareDeleted(Request $request): JsonResponse
    {
        $data = $this->resolveCloudShareDeletedTotals($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        return response()->json([
            'status' => true,
            'metric' => 'cloudShareDeleted',
            'data' => $data,
        ]);
    }

    /**
     * Resolve total users using the parsed date range.
     *
     * @param Request $request Request containing optional date filters.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveTotalUsers(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, null);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;
        $query = User::query();

        if ($startDate || $endDate) {
            $query->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));
        }

        return $query->count();
    }

    /**
     * Resolve registrations using the parsed date range.
     *
     * @param Request $request Request containing optional date filters.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveRegisters(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, null);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return User::query()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->count();
    }

    /**
     * Resolve monthly registrations using the month preset.
     *
     * @param Request $request Request with optional date overrides.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveMonthlyRegisters(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, 'month');
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return User::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Resolve weekly registrations using the week preset.
     *
     * @param Request $request Request with optional date overrides.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveWeeklyRegisters(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, 'week');
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return User::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Resolve login events using the parsed date range.
     *
     * @param Request $request Request containing optional date filters.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveLogins(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, null);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return RecorderModel::query()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->count();
    }

    /**
     * Resolve monthly login events using the month preset.
     *
     * @param Request $request Request with optional date overrides.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveMonthlyLogins(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, 'month');
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return RecorderModel::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Resolve weekly login events using the week preset.
     *
     * @param Request $request Request with optional date overrides.
     * @return int|JsonResponse Integer count or validation error response.
     */
    protected function resolveWeeklyLogins(Request $request): int|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, 'week');
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        return RecorderModel::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Resolve cloud share totals and total size for the parsed range.
     *
     * @param Request $request Request containing optional date filters.
     * @return array|JsonResponse Aggregate values or validation error response.
     */
    protected function resolveCloudShareTotals(Request $request): array|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, null);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        $query = CloudShare::withTrashed()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));

        $countQuery = clone $query;
        $sizeQuery = clone $query;

        return [
            'total' => $countQuery->count(),
            'size' => (int) (($sizeQuery->sum('size')) ?? 0),
        ];
    }

    /**
     * Resolve active cloud share totals and size for the parsed range.
     *
     * @param Request $request Request containing optional date filters.
     * @param string|null $preset Optional date preset (for example week/month).
     * @return array|JsonResponse Aggregate values or validation error response.
     */
    protected function resolveCloudShareActiveTotals(Request $request, ?string $preset = null): array|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, $preset);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        $query = CloudShare::query()
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));

        $countQuery = clone $query;
        $sizeQuery = clone $query;

        return [
            'total' => $countQuery->count(),
            'size' => (int) (($sizeQuery->sum('size')) ?? 0),
        ];
    }

    /**
     * Resolve deleted cloud share totals and size for the parsed range.
     *
     * @param Request $request Request containing optional date filters.
     * @param string|null $preset Optional date preset (for example week/month).
     * @return array|JsonResponse Aggregate values or validation error response.
     */
    protected function resolveCloudShareDeletedTotals(Request $request, ?string $preset = null): array|JsonResponse
    {
        $range = $this->resolveRangeFromRequest($request, $preset);
        if ($range instanceof JsonResponse) {
            return $range;
        }

        [$startDate, $endDate] = $range;

        $query = CloudShare::withTrashed()
            ->whereNotNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));

        $countQuery = clone $query;
        $sizeQuery = clone $query;

        return [
            'total' => $countQuery->count(),
            'size' => (int) (($sizeQuery->sum('size')) ?? 0),
        ];
    }

    /**
     * Parse and validate date range query parameters with optional presets.
     *
     * @param Request $request Request containing startDate and endDate query params.
     * @param string|null $preset Optional preset (month/week) for default bounds.
     * @return array|JsonResponse Parsed [startDate, endDate] or validation error response.
     */
    protected function resolveRangeFromRequest(Request $request, ?string $preset = null): array|JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid date range parameters.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $startInput = $request->query('startDate');
        $endInput = $request->query('endDate');

        if ($preset === 'month') {
            $defaultStart = now()->startOfMonth();
            $defaultEnd = now()->endOfDay();
        } elseif ($preset === 'week') {
            $defaultStart = now()->startOfWeek();
            $defaultEnd = now()->endOfDay();
        } else {
            $defaultStart = null;
            $defaultEnd = null;
        }

        $startDate = $startInput
            ? Carbon::parse($startInput)->startOfDay()
            : $defaultStart;
        $endDate = $endInput
            ? Carbon::parse($endInput)->endOfDay()
            : ($startInput ? now()->endOfDay() : $defaultEnd);

        if ($startDate && $endDate && $startDate->gt($endDate)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid date range parameters.',
                'errors' => ['The startDate must be before or equal to endDate.'],
            ], 422);
        }

        return [$startDate, $endDate];
    }

    /**
     * Build a standard metric response payload.
     *
     * @param string $metric Metric name identifier.
     * @param int $value Metric value.
     * @return JsonResponse JSON response for simple metric endpoints.
     */
    protected function metricResponse(string $metric, int $value): JsonResponse
    {
        return response()->json([
            'status' => true,
            'metric' => $metric,
            'value' => $value,
        ]);
    }
}
