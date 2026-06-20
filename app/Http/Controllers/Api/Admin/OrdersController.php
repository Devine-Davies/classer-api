<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Admin\OrderTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function __construct(private readonly OrderTableService $orderTableService) {}

    /**
     * List orders with optional status/search filters and pagination.
     *
     * @param  Request  $request  Request with status, q, and limit query parameters.
     * @return JsonResponse Paginated orders response.
     */
    public function index(Request $request): JsonResponse
    {
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $search = trim((string) $request->query('q', ''));
        $orders = $this->orderTableService->paginate($request);
        $statusOptions = $this->orderTableService->statusOptions();

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
        $order = Order::with(['items.catalogItem', 'payments'])
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
                    // 'refunded_at' => $payment->refunded_at,
                ];
            }),
        ]);
    }
}
