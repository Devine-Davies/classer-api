<?php

namespace App\Services\Admin;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for handling admin plan table operations such as pagination and filtering.
 *
 * Provides methods to build paginated lists of plans for the admin interface, with support for search filters.
 * Used by PlansController to retrieve and display plan data in the admin panel.
 */
class PlansService
{
    /**
     * Build paginated plans list for the admin plans table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));
        $query = Plan::with('catalogItem')->latest('updated_at')->latest('id');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('uid', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhere('duration', 'like', $like)
                    ->orWhereHas('catalogItem', function ($catalogQuery) use ($like) {
                        $catalogQuery
                            ->where('title', 'like', $like)
                            ->orWhere('sku', 'like', $like)
                            ->orWhere('slug', 'like', $like);
                    });
            });
        }

        return $query->paginate($limit)->appends($request->query());
    }

    /**
     * Get a plan by UID.
     */
    public function getByUid(string $planUid): ?Plan
    {
        return Plan::with('catalogItem')->where('uid', $planUid)->first();
    }

    /**
     * Create or update a Plan with the provided data and return the model instance.
     */
    public function upsert(array $data): Plan
    {
        // Create the plan with the associated catalog item ID if it exists
        return Plan::updateOrCreate(
            ['uid' => $data['uid'] ?? null],
            [
                'code' => $data['code'],
                'title' => $data['title'],
                'quota' => $data['quota'] ?? null,
                'type' => $data['type'] ?? null,
                'duration' => $data['duration'] ?? null,
                'promotion_eligible' => $data['promotion_eligible'] ?? false,
                'discount_code_eligible' => $data['discount_code_eligible'] ?? false,
                'shipping_required' => $data['shipping_required'] ?? false,
            ]
        );
    }
}
