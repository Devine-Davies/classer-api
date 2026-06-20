<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checkout\PaymentIntentRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\Web\Checkout\SessionResource;
use App\Models\Order;
use App\Services\DiscountCodeService;
use App\Services\OrderCheckoutService;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderCheckoutService $orderCheckoutService,
        protected DiscountCodeService $discountCodeService,
        protected StripePaymentService $stripePaymentService
    ) {}

    /**
     * Create a new payment intent for the specified order.
     *
     * @param  PaymentIntentRequest  $request  Validated payment intent request.
     * @param  string  $orderUid  UID of the order.
     * @return JsonResponse Payment intent response with client secret and order details.
     */
    public function createPaymentIntent(PaymentIntentRequest $request, string $orderUid): JsonResponse
    {
        $validated = $request->validated();

        $order = Order::where('uid', $orderUid)
            ->where('status', 'pending')
            ->with(['items.catalogItem', 'discountCode'])
            ->firstOrFail();

        $order = $this->orderCheckoutService->hydrateCustomerDetails($order, $validated);
        $order = $this->discountCodeService->finalizeForPaymentIntent(
            $order,
            $validated['discount_code'] ?? null,
            (string) $validated['customer_email']
        );

        $intent = $this->stripePaymentService->createOrGetPaymentIntent($order);

        return response()->json(
            new SessionResource([
                'client_secret' => $intent['client_secret'],
                'payment' => $intent['payment'],
                'order' => $order->fresh()->load(['items.catalogItem', 'discountCode']),
            ])
        );
    }

    /**
     * Apply a discount code to the specified order.
     *
     * @param  Request  $request  Incoming request with discount code and customer email.
     * @param  string  $orderUid  UID of the order.
     * @return JsonResponse Response with discount application result.
     */
    public function applyDiscount(Request $request, string $orderUid): JsonResponse
    {
        try {
            $validated = $request->validate([
                'discount_code' => ['nullable', 'string', 'max:64'],
                'customer_email' => ['nullable', 'email', 'max:120'],
            ]);

            $order = Order::where('uid', $orderUid)
                ->where('status', 'pending')
                ->with(['items.catalogItem', 'discountCode'])
                ->firstOrFail();

            $order = $this->discountCodeService->applyPreview(
                $order,
                $validated['discount_code'] ?? null,
                $validated['customer_email'] ?? null,
                null,
                true
            );

            return response()->json([
                'status' => true,
                'is_valid' => true,
                'reason_code' => null,
                'code' => $order->discount_snapshot['code'] ?? null,
                'pricing_preview' => [
                    'subtotal' => $order->subtotal_amount,
                    'discount' => $order->discount_amount,
                    'total' => $order->total_amount,
                    'currency' => $order->currency,
                ],
                'order' => new OrderResource($order->load(['items.catalogItem', 'discountCode'])),
            ]);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $reasonCode = $errors['reason_code'][0] ?? DiscountCodeService::REASON_NOT_ELIGIBLE;

            return response()->json([
                'status' => false,
                'is_valid' => false,
                'reason_code' => $reasonCode,
                'code' => null,
                'pricing_preview' => null,
                'message' => 'Discount code is not eligible.',
                'errors' => $errors,
            ], 422);
        }
    }
}
