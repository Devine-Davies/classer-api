<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * List orders with optional status/search filters and pagination.
     *
     * @param  Request  $request  Request with status, q, and limit query parameters.
     * @return JsonResponse Paginated orders response.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $search = trim((string) $request->query('q', ''));

        $query = Order::with(['product', 'items.product'])->latest('id');

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
                    ->orWhereHas('product', function ($productQuery) use ($like) {
                        $productQuery->where('name', 'like', $like);
                    })
                    ->orWhereHas('items.product', function ($productQuery) use ($like) {
                        $productQuery->where('name', 'like', $like);
                    });
            });
        }

        $orders = $query->paginate($limit)->appends($request->query());

        $statusOptions = Order::query()
            ->select('status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->map(fn ($value) => strtolower((string) $value))
            ->values();

        return response()->json([
            'status' => true,
            'data' => OrderResource::collection($orders->items()),
            'status_options' => $statusOptions,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    /**
     * Return an order detail payload by UID including payments.
     *
     * @param  string  $orderUid  Order UID.
     * @return JsonResponse Order detail response.
     */
    public function show(string $orderUid): JsonResponse
    {
        $order = Order::with(['product', 'items.product', 'payments'])
            ->where('uid', $orderUid)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new OrderResource($order),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'uid' => $payment->uid,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
                    'failure_code' => $payment->failure_code,
                    'failure_message' => $payment->failure_message,
                    'paid_at' => $payment->paid_at,
                    'refunded_at' => $payment->refunded_at,
                ];
            }),
        ]);
    }
}
