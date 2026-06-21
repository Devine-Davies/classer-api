<?php

namespace App\Services\Admin;

use App\Models\CloudShare;
use App\Models\RecorderModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StatsService
{
    /**
     * Get statistics based on provided filters and preset.
     */
    public function getStats(array $filters = [], ?string $preset = null): array
    {
        return [
            'totalUsers' => $this->totalUsers($filters),
            'registers' => $this->registers($filters, $preset),
            'logins' => $this->logins($filters, $preset),
            'cloudShares' => $this->cloudShares($filters),
            'activeCloudShares' => $this->activeCloudShares($filters, $preset),
            'deletedCloudShares' => $this->deletedCloudShares($filters, $preset),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function totalUsers(array $filters = []): int
    {
        [$startDate, $endDate] = $this->resolveRange($filters);

        return User::query()
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->count();
    }

    /**
     * @throws ValidationException
     */
    public function registers(array $filters = [], ?string $preset = null): int
    {
        [$startDate, $endDate] = $this->resolveRange($filters, $preset);

        return User::query()
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->when($startDate && ! $endDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when(! $startDate && $endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->count();
    }

    /**
     * @throws ValidationException
     */
    public function logins(array $filters = [], ?string $preset = null): int
    {
        [$startDate, $endDate] = $this->resolveRange($filters, $preset);

        return RecorderModel::query()
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->when($startDate && ! $endDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when(! $startDate && $endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->count();
    }

    /**
     * @throws ValidationException
     */
    public function cloudShares(array $filters = []): array
    {
        [$startDate, $endDate] = $this->resolveRange($filters);

        $query = CloudShare::withTrashed()
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate));

        return $this->cloudShareAggregate($query);
    }

    /**
     * @throws ValidationException
     */
    public function activeCloudShares(array $filters = [], ?string $preset = null): array
    {
        [$startDate, $endDate] = $this->resolveRange($filters, $preset);

        $query = CloudShare::query()
            ->whereNull('deleted_at')
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate));

        return $this->cloudShareAggregate($query);
    }

    /**
     * @throws ValidationException
     */
    public function deletedCloudShares(array $filters = [], ?string $preset = null): array
    {
        [$startDate, $endDate] = $this->resolveRange($filters, $preset);

        $query = CloudShare::withTrashed()
            ->whereNotNull('deleted_at')
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate));

        return $this->cloudShareAggregate($query);
    }

    protected function cloudShareAggregate($query): array
    {
        $countQuery = clone $query;
        $sizeQuery = clone $query;

        return [
            'total' => $countQuery->count(),
            'size' => (int) ($sizeQuery->sum('size') ?? 0),
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function resolveRange(array $filters = [], ?string $preset = null): array
    {
        $validator = Validator::make($filters, [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $startInput = $filters['startDate'] ?? null;
        $endInput = $filters['endDate'] ?? null;

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

        return [$startDate, $endDate];
    }
}
