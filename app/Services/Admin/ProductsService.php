<?php

namespace App\Services\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsService
{
    /**
     * Build paginated products list for the admin products table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));

        $query = Product::with('catalogItem')->latest('updated_at')->latest('id');
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('uid', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhereHas('catalogItem', function ($catalogQuery) use ($like) {
                        $catalogQuery
                            ->where('title', 'like', $like)
                            ->orWhere('sku', 'like', $like)
                            ->orWhere('slug', 'like', $like);
                    });
            });
        }

        return $query->paginate($limit);
    }

    /**
     * Get a product by UID.
     */
    public function getByUid(string $productUid): ?Product
    {
        return Product::with('catalogItem')
            ->where('uid', $productUid)
            ->first();
    }

    /**
     * Create a new Product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing Product by UID and return the updated model.
     */
    public function update(string $uuid, array $data): Product
    {
        $product = Product::where('uid', $uuid)->firstOrFail();

        // Update the product with the provided data
        $product->update($data);

        // Sync the catalog item if provided in the data
        if (isset($data['catalog_item'])) {
            $product->syncCatalogItem($data['catalog_item']);
        }

        return $product->refresh()->load('catalogItem');
    }
}
