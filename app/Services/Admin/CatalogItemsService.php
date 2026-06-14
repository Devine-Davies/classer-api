<?php

namespace App\Services\Admin;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogItemsService
{
    /**
     * Build paginated catalog items list for the admin table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));
        $sellableType = trim((string) $request->query('sellable_type', 'all'));
        $isPublished = trim((string) $request->query('is_published', 'all'));

        $query = CatalogItem::query()
            ->with('sellable')
            ->latest('updated_at')
            ->latest('id');

        if ($sellableType !== '' && $sellableType !== 'all') {
            $query->where('sellable_type', $sellableType);
        }

        if ($isPublished === 'yes') {
            $query->where('is_published', true);
        } elseif ($isPublished === 'no') {
            $query->where('is_published', false);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('uid', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('sellable_id', 'like', $like);
            });
        }

        return $query->paginate($limit)->appends($request->query());
    }

    /**
     * Get a catalog item by UID.
     */
    public function getByUid(string $catalogItemUid): ?CatalogItem
    {
        return CatalogItem::with('sellable')->where('uid', $catalogItemUid)->first();
    }

    /**
     * Create or update a CatalogItem with the provided data and return the model instance.
     */
    public function create(array $data): CatalogItem
    {
        return CatalogItem::create($data);
    }

    /**
     * Update a CatalogItem with the provided data and return the number of affected rows.
     */
    public function update(array $data): int
    {
        return CatalogItem::where('uid', $data['uid'])->update($data);
    }

    public function getAllProducts()
    {
        return app(ProductsService::class)->paginate(new Request(['limit' => 1000]))->getCollection();
    }

    public function getAllPlans()
    {
        return app(PlansService::class)->paginate(new Request(['limit' => 1000]))->getCollection();
    }
}
