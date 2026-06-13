<?php

namespace App\Services\Admin;

use App\Models\DiscountCode;
use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscountCodesService
{
    /**
     * Build paginated discount codes list for the admin table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));

        $query = DiscountCode::query()
            ->with('catalogItem')
            ->latest('updated_at')
            ->latest('id');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('code', 'like', $like)
                    ->orWhere('assigned_email', 'like', $like)
                    ->orWhere('internal_note', 'like', $like);
            });
        }

        return $query->paginate($limit)->appends($request->query());
    }

    /**
     * Get CatalogItem options for the products form select input.
     */
    public function catalogItems()
    {
        // only if is_active
        return CatalogItem::query()->where('is_active', true)->latest('updated_at')->latest('id')->get(['uid', 'title']);
    }

    /**
     * Get a discount code by its UID.
     */
    public function getByUid(string $uid): ?DiscountCode
    {
        return DiscountCode::with('catalogItem')->where('uid', $uid)->first();
    }

    /**
     * Create a new discount code with the provided data.
     */
    public function create(array $data): DiscountCode
    {
        return DiscountCode::create($data);
    }

    /**
     * Update a discount code with the provided data.
     */
    public function update(array $data): bool
    {
        return DiscountCode::where('uid', $data['uid'])->update($data) > 0;
    }
}
