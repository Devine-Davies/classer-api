<?php

namespace App\Services\Admin;

use App\Enums\CloudStorageKind;
use App\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlansService
{
    private const DEFAULT_LIMIT = 20;

    private const MAX_LIMIT = 100;

    /**
     * Build a paginated plans list for the admin plans table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = $this->getPaginationLimit($request);
        $search = trim((string) $request->query('q', ''));

        $query = $this->baseQuery();

        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        return $query
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * Get a plan by UID.
     */
    public function getByUid(string $planUid): ?Plan
    {
        return $this->baseQuery()
            ->where('uid', $planUid)
            ->first();
    }

    /**
     * Create a new Plan.
     */
    public function create(array $data): Plan
    {
        return DB::transaction(function () use ($data): Plan {
            $entitlements = $data['entitlements'] ?? [];
            unset($data['entitlements']);

            $plan = Plan::create($data);
            $this->syncEntitlements($plan, $entitlements);

            return $plan->load(['catalogItem', 'entitlements']);
        });
    }

    /**
     * Update an existing Plan by UID and return the updated model.
     */
    public function update(string $planUid, array $data): Plan
    {
        return DB::transaction(function () use ($planUid, $data): Plan {
            $plan = Plan::where('uid', $planUid)->lockForUpdate()->firstOrFail();
            $catalogItem = $data['catalog_item'] ?? null;
            $entitlements = $data['entitlements'] ?? [];
            unset($data['catalog_item'], $data['entitlements']);

            $plan->update($data);
            $this->syncEntitlements($plan, $entitlements);

            if ($catalogItem !== null) {
                $plan->syncCatalogItem($catalogItem);
            }

            return $plan->refresh()->load(['catalogItem', 'entitlements']);
        });
    }

    public function setPublished(string $planUid, bool $isPublished): bool
    {
        $plan = Plan::with('catalogItem')
            ->where('uid', $planUid)
            ->first();

        if (! $plan) {
            return false;
        }

        $plan->syncCatalogItem([
            'is_published' => $isPublished,
        ]);

        return true;
    }

    /**
     * Base query used by admin plan screens.
     */
    private function baseQuery(): Builder
    {
        return Plan::query()
            ->with(['catalogItem', 'entitlements'])
            ->latest('updated_at')
            ->latest('id');
    }

    private function syncEntitlements(Plan $plan, array $entitlements): void
    {
        $managedCapabilities = array_map(
            fn (CloudStorageKind $kind): string => $kind->capability(),
            CloudStorageKind::cases()
        );
        $selectedCapabilities = array_keys($entitlements);

        $plan->entitlements()
            ->whereIn('capability', $managedCapabilities)
            ->when(
                $selectedCapabilities !== [],
                fn ($query) => $query->whereNotIn('capability', $selectedCapabilities)
            )
            ->delete();

        foreach ($entitlements as $capability => $quota) {
            $plan->entitlements()->updateOrCreate(
                ['capability' => $capability],
                ['quota' => $quota]
            );
        }
    }

    /**
     * Apply admin table search filters.
     */
    private function applySearch(Builder $query, string $search): void
    {
        $like = '%'.$search.'%';

        $query->where(function (Builder $nested) use ($like): void {
            $nested
                ->where('uid', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('title', 'like', $like)
                ->orWhere('duration', 'like', $like)
                ->orWhereHas('entitlements', fn (Builder $entitlementQuery) => $entitlementQuery->where('capability', 'like', $like))
                ->orWhereHas('catalogItem', function (Builder $catalogQuery) use ($like): void {
                    $catalogQuery
                        ->where('title', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                });
        });
    }

    /**
     * Normalise pagination limit from request query.
     */
    private function getPaginationLimit(Request $request): int
    {
        $limit = (int) $request->query('limit', self::DEFAULT_LIMIT);

        return max(1, min($limit, self::MAX_LIMIT));
    }
}
