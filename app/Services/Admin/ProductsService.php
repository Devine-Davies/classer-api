<?php

namespace App\Services\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsService
{
    /**
     * Build paginated plans list for the admin plans table.
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
     * Find a product by UID or fail.
     */
    public function findByUid(string $productUid): Product
    {
        return Product::with('catalogItem')->where('uid', $productUid)->firstOrFail();
    }

    /**
    * Create a Product with the provided data and return the model instance.
    */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a Product with the provided data and return the number of affected rows.
     */
    public function update(array $data): int
    {
        return Product::where('uid', $data['uid'])->update($data);
    }
}
