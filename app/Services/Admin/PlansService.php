<?php

namespace App\Services\Admin;

use App\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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
        return Plan::create($data);
    }

    /**
     * Update an existing Plan by UID and return the updated model.
     */
    public function update(string $planUid, array $data): Plan
    {
        $plan = Plan::where('uid', $planUid)->firstOrFail();

        // Update the plan with the provided data
        $plan->update($data);

        // Sync the catalog item if provided in the data
        if (isset($data['catalogItem'])) {
            $plan->syncCatalogItem($data['catalogItem']);
        }

        return $plan->refresh()->load('catalogItem');
    }

    /**
     * Base query used by admin plan screens.
     */
    private function baseQuery(): Builder
    {
        return Plan::query()
            ->with('catalogItem')
            ->latest('updated_at')
            ->latest('id');
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
                ->orWhere('type', 'like', $like)
                ->orWhere('duration', 'like', $like)
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
