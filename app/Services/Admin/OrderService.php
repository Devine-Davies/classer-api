<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Build paginated orders list for the admin orders table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $search = trim((string) $request->query('q', ''));

        $query = Order::with(['items.catalogItem', 'payments'])->latest('id');

        if ($status !== '' && $status !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('uid', 'like', $like)
                    ->orWhere('customer_email', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhereHas('items.catalogItem', function ($catalogQuery) use ($like) {
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
     * Find an order by its UID, including related product and catalog item data.
     */
    public function findByUid(string $orderUid): ?Order
    {
        return Order::with(['items.catalogItem', 'payments'])
            ->where('uid', $orderUid)
            ->first();
    }

    /**
     * Build available status options for the admin orders filter.
     */
    public function statusOptions(): Collection
    {
        return Order::query()
            ->select('status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->map(fn ($value) => strtolower((string) $value))
            ->values();
    }
}
