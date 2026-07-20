<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Checkout\StartRequest;
use App\Http\Requests\Web\Checkout\StoreDetailsRequest;
use App\Http\Resources\Web\Checkout\SuccessResource;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Services\DiscountCodeService;
use App\Services\OrderCheckoutService;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use stdClass;

class CheckoutController extends Controller
{
    private const SESSION_DRAFT = 'checkout_draft';

    private const SESSION_EDIT_ORDER_UID = 'checkout_edit_order_uid';

    private const SESSION_ORDER_UIDS = 'checkout_order_uids';

    public function __construct(
        protected OrderCheckoutService $orderCheckoutService,
        protected DiscountCodeService $discountCodeService,
        protected StripePaymentService $stripePaymentService
    ) {}

    /**
     * Entry point for the checkout area.
     *
     * Checkout always begins from a product via checkout.start, so send the
     * visitor to their in-progress details page when a draft exists, otherwise
     * back to the home page.
     */
    public function index(Request $request): RedirectResponse
    {
        return $this->getDraft($request)
            ? redirect()->route('checkout.details')
            : redirect('/');
    }

    /**
     * Start the checkout process for one or more catalog items.
     */
    public function start(StartRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $catalogItemUids = array_values(array_filter((array) ($validated['catalog_item_uids'] ?? [])));

        $catalogItemSkus = array_values(array_filter([
            ...((array) ($validated['catalog_item_skus'] ?? [])),
            $validated['catalog_item_sku'] ?? null,
        ]));

        if (empty($catalogItemUids) && empty($catalogItemSkus)) {
            return redirect('/');
        }

        $catalogItems = $this->getPublishedCatalogItems(
            catalogItemUids: $catalogItemUids,
            catalogItemSkus: $catalogItemSkus
        );

        if ($catalogItems->isEmpty()) {
            return redirect('/');
        }

        $catalogItemUids = $this->resolveCatalogItemUids(
            catalogItems: $catalogItems,
            preferredUids: $catalogItemUids,
            preferredSkus: $catalogItemSkus
        );

        $quantities = $this->normalizeQuantities(
            catalogItems: $catalogItems,
            rawQuantities: (array) ($validated['quantities'] ?? [])
        );

        $draft = [
            'catalog_item_uids' => $catalogItemUids,
            'quantities' => $quantities,

            'customer_name' => '',
            'customer_email' => '',

            'shipping_line_1' => '',
            'shipping_line_2' => '',
            'shipping_city' => '',
            'shipping_state' => '',
            'shipping_postal_code' => '',
            'shipping_country' => 'GB',

            'discount_code' => '',
            'discount_snapshot' => null,

            'subtotal_amount' => null,
            'discount_amount' => null,
            'total_amount' => null,
        ];

        $request->session()->put(self::SESSION_DRAFT, $draft);
        $request->session()->forget(self::SESSION_EDIT_ORDER_UID);

        return redirect()->route('checkout.details');
    }

    /**
     * Continue checkout for an existing order.
     */
    public function checkout(string $orderUid): RedirectResponse
    {
        $order = $this->findOrder($orderUid);

        if (! $order) {
            return redirect('/');
        }

        return redirect()->route('checkout.payment', [
            'orderUid' => $order->uid,
        ]);
    }

    /**
     * Show the checkout details page.
     */
    public function details(Request $request): View|RedirectResponse
    {
        $draft = $this->getDraft($request);

        if (! $draft) {
            return redirect()->route('checkout.index');
        }

        $catalogItemUids = array_values(array_filter((array) ($draft['catalog_item_uids'] ?? [])));

        if (empty($catalogItemUids)) {
            $this->clearCheckoutSession($request);

            return redirect('/');
        }

        $catalogItems = CatalogItem::query()
            ->with('sellable')
            ->whereIn('uid', $catalogItemUids)
            ->where('is_published', true)
            ->get()
            ->keyBy('uid');

        if ($catalogItems->isEmpty()) {
            $this->clearCheckoutSession($request);

            return redirect('/');
        }

        $order = $this->buildDraftOrderViewModel($draft, $catalogItems->all());

        return view('checkout.details', [
            'order' => $order,
            'checkoutDraft' => (object) $draft,
        ]);
    }

    /**
     * Store customer and delivery details, optionally apply discount,
     * then continue to payment.
     */
    public function storeDetails(StoreDetailsRequest $request): RedirectResponse
    {
        $formAction = (string) $request->input('form_action', 'continue');

        $draft = $this->getDraft($request);

        if (! $draft) {
            return redirect()->route('checkout.index');
        }

        $validated = $request->validated();

        $draft = array_merge($draft, $validated);
        $request->session()->put(self::SESSION_DRAFT, $draft);

        $editingOrderUid = $request->session()->get(self::SESSION_EDIT_ORDER_UID);
        $order = $editingOrderUid ? $this->findOrder((string) $editingOrderUid) : null;

        if (! $order) {
            $catalogItemUids = array_values(array_filter((array) ($draft['catalog_item_uids'] ?? [])));
            $quantities = (array) ($draft['quantities'] ?? []);

            if (empty($catalogItemUids)) {
                $this->clearCheckoutSession($request);

                return redirect('/');
            }

            $order = $this->orderCheckoutService->createPendingOrder(
                catalogItemUids: $catalogItemUids,
                quantities: $quantities
            );
        }

        $order = $this->orderCheckoutService->hydrateCustomerDetails($order, $validated);
        $order = $this->orderCheckoutService->refreshOrderLinePricingFromCatalog($order);
        $this->rememberOrder($request, (string) $order->uid);

        try {
            $order = $this->discountCodeService->applyPreview(
                order: $order,
                discountCodeInput: $validated['discount_code'] ?? null,
                customerEmail: $validated['customer_email'] ?? null,
            );
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        $draft = array_merge($draft, [
            'discount_code' => data_get($order->discount_snapshot, 'code'),
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'discount_snapshot' => $order->discount_snapshot,
        ]);

        $request->session()->put(self::SESSION_DRAFT, $draft);
        $request->session()->put(self::SESSION_EDIT_ORDER_UID, $order->uid);

        if ($formAction === 'apply_discount') {
            return redirect()
                ->route('checkout.details')
                ->with(
                    'checkout_status',
                    filled($validated['discount_code'] ?? null)
                        ? 'Discount code applied.'
                        : 'Discount code removed.'
                );
        }

        $request->session()->forget(self::SESSION_EDIT_ORDER_UID);

        return redirect()->route('checkout.payment', [
            'orderUid' => $order->uid,
        ]);
    }

    /**
     * Show the checkout payment page.
     */
    public function payment(Request $request, string $orderUid): View|RedirectResponse
    {
        if (! $this->ownsOrder($request, $orderUid)) {
            return redirect('/');
        }

        $order = Order::query()
            ->where('uid', $orderUid)
            ->where('status', 'pending')
            ->with(['items.catalogItem', 'discountCode'])
            ->first();

        if (! $order) {
            $this->clearCheckoutSession($request);

            return redirect('/');
        }

        if (! $this->hasDetails($order) || ! $this->hasDelivery($order)) {
            return redirect()->route('checkout.details');
        }

        $draft = $this->getDraft($request);

        if ($draft) {
            $order = $this->orderCheckoutService->hydrateCustomerDetails($order, $draft);
            $order = $this->orderCheckoutService->refreshOrderLinePricingFromCatalog($order);

            $order = $this->discountCodeService->finalizeForPaymentIntent(
                order: $order,
                discountCodeInput: data_get($draft, 'discount_snapshot.code'),
                customerEmail: (string) ($draft['customer_email'] ?? $order->customer_email)
            );
        }

        $intent = $this->stripePaymentService->createOrGetPaymentIntent($order);

        return view('checkout.payment', [
            'checkoutDraft' => $draft ? (object) $draft : null,
            'order' => $order,
            'stripePublishableKey' => (string) config('services.stripe.key'),
            'stripeClientSecret' => (string) $intent['client_secret'],
        ]);
    }

    /**
     * Show the checkout success page.
     */
    public function success(Request $request, string $orderUid): View|RedirectResponse
    {
        if (! $this->ownsOrder($request, $orderUid)) {
            return redirect('/');
        }

        $order = Order::query()
            ->where('uid', $orderUid)
            ->first();

        $order = $order?->fresh()->load(['items.catalogItem', 'discountCode']);

        if (! $order) {
            $this->clearCheckoutSession($request);

            return redirect('/');
        }

        return view('checkout.success', [
            'order' => SuccessResource::make($order)->resolve($request),
        ]);
    }

    /**
     * Get the current checkout draft from the session.
     */
    protected function getDraft(Request $request): ?array
    {
        $draft = $request->session()->get(self::SESSION_DRAFT);

        return is_array($draft) ? $draft : null;
    }

    /**
     * Clear checkout-related session data.
     */
    protected function clearCheckoutSession(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_DRAFT,
            self::SESSION_EDIT_ORDER_UID,
        ]);
    }

    /**
     * Record an order UID as owned by the current session so the checkout and
     * confirmation pages can only be viewed by the visitor who created it.
     */
    protected function rememberOrder(Request $request, string $orderUid): void
    {
        if ($orderUid === '') {
            return;
        }

        $orderUids = (array) $request->session()->get(self::SESSION_ORDER_UIDS, []);
        $orderUids[] = $orderUid;

        $request->session()->put(
            self::SESSION_ORDER_UIDS,
            array_values(array_unique(array_filter($orderUids)))
        );
    }

    /**
     * Determine whether the current session owns the given order UID.
     */
    protected function ownsOrder(Request $request, string $orderUid): bool
    {
        $orderUids = (array) $request->session()->get(self::SESSION_ORDER_UIDS, []);

        return in_array($orderUid, $orderUids, true);
    }

    /**
     * Get published catalog items by UID or SKU.
     *
     * @return Collection<string, CatalogItem>
     */
    protected function getPublishedCatalogItems(array $catalogItemUids, array $catalogItemSkus): Collection
    {
        return CatalogItem::query()
            ->with('sellable')
            ->where('is_published', true)
            ->when(! empty($catalogItemUids), function ($query) use ($catalogItemUids) {
                $query->whereIn('uid', $catalogItemUids);
            })
            ->when(empty($catalogItemUids) && ! empty($catalogItemSkus), function ($query) use ($catalogItemSkus) {
                $query->whereIn('sku', $catalogItemSkus);
            })
            ->get()
            ->keyBy('uid');
    }

    /**
     * Resolve selected catalog items into a stable UID list while preserving submitted order.
     */
    protected function resolveCatalogItemUids(
        Collection $catalogItems,
        array $preferredUids,
        array $preferredSkus
    ): array {
        if (! empty($preferredUids)) {
            return collect($preferredUids)
                ->filter(fn (string $uid): bool => $catalogItems->has($uid))
                ->values()
                ->all();
        }

        return collect($preferredSkus)
            ->map(function (string $sku) use ($catalogItems): ?string {
                $catalogItem = $catalogItems->firstWhere('sku', $sku);

                return $catalogItem?->uid;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize quantities so they are always keyed by catalog item UID.
     */
    protected function normalizeQuantities(Collection $catalogItems, array $rawQuantities): array
    {
        $quantities = [];

        foreach ($catalogItems as $catalogItem) {
            $rawQuantity = $rawQuantities[$catalogItem->uid]
                ?? $rawQuantities[$catalogItem->sku]
                ?? 1;

            $quantities[$catalogItem->uid] = max(1, min(99, (int) $rawQuantity));
        }

        return $quantities;
    }

    /**
     * Build a draft order view model for the checkout details page.
     */
    protected function buildDraftOrderViewModel(array $draft, array $catalogItems): stdClass
    {
        $quantities = (array) ($draft['quantities'] ?? []);

        $lineItems = array_map(function (CatalogItem $catalogItem) use ($draft, $quantities): array {
            $quantity = max(
                1,
                min(
                    99,
                    (int) ($quantities[$catalogItem->uid] ?? $draft['quantity'] ?? 1)
                )
            );

            $pricing = $this->calculateCatalogItemPricing($catalogItem);

            return [
                'catalog_item_uid' => $catalogItem->uid,
                'name_snapshot' => $catalogItem->title,
                'sku_snapshot' => $catalogItem->sku,
                'unit_amount' => $pricing['unit_amount'],
                'original_unit_amount' => $pricing['original_unit_amount'],
                'promotion_percentage' => $pricing['promotion_percentage'],
                'quantity' => $quantity,
                'line_amount' => $pricing['unit_amount'] * $quantity,
                'original_line_amount' => $pricing['original_unit_amount'] * $quantity,
                'currency' => strtolower((string) ($catalogItem->currency ?? 'gbp')),
                'image_url' => $catalogItem->image_url,
            ];
        }, $catalogItems);

        $subtotalAmount = array_reduce(
            $lineItems,
            static fn (int $total, array $item): int => $total + (int) ($item['original_line_amount'] ?? 0),
            0
        );

        $totalBeforeDiscountCode = array_reduce(
            $lineItems,
            static fn (int $total, array $item): int => $total + (int) ($item['line_amount'] ?? 0),
            0
        );

        $promotionDiscountAmount = max(0, $subtotalAmount - $totalBeforeDiscountCode);

        $currency = strtolower((string) (
            $lineItems[0]['currency']
            ?? $draft['currency']
            ?? 'gbp'
        ));

        return (object) [
            'uid' => null,
            'line_items' => $lineItems,

            'amount' => $totalBeforeDiscountCode,
            'subtotal_amount' => (int) ($draft['subtotal_amount'] ?? $subtotalAmount),
            'discount_amount' => (int) ($draft['discount_amount'] ?? $promotionDiscountAmount),
            'total_amount' => (int) ($draft['total_amount'] ?? $totalBeforeDiscountCode),
            'currency' => $currency,

            'customer_name' => $draft['customer_name'] ?? null,
            'customer_email' => $draft['customer_email'] ?? null,

            'shipping_line_1' => $draft['shipping_line_1'] ?? null,
            'shipping_line_2' => $draft['shipping_line_2'] ?? null,
            'shipping_city' => $draft['shipping_city'] ?? null,
            'shipping_state' => $draft['shipping_state'] ?? null,
            'shipping_postal_code' => $draft['shipping_postal_code'] ?? null,
            'shipping_country' => strtoupper((string) ($draft['shipping_country'] ?? 'GB')),

            'discount_code' => $draft['discount_code'] ?? null,
            'discount_snapshot' => $draft['discount_snapshot'] ?? null,

            'status' => 'draft',
        ];
    }

    /**
     * Calculate pricing values for a catalog item.
     */
    protected function calculateCatalogItemPricing(CatalogItem $catalogItem): array
    {
        $pricing = $catalogItem->pricingBreakdown();

        return [
            'original_unit_amount' => $pricing['original_unit_amount'],
            'unit_amount' => $pricing['unit_amount'],
            'promotion_percentage' => $pricing['promotion_percentage'],
        ];
    }

    /**
     * Find an order by UID.
     */
    protected function findOrder(string $orderUid): ?Order
    {
        return Order::query()
            ->with(['items.catalogItem', 'discountCode'])
            ->where('uid', $orderUid)
            ->first();
    }

    /**
     * Check whether an order has customer details.
     */
    protected function hasDetails(Order $order): bool
    {
        return filled($order->customer_name)
            && filled($order->customer_email);
    }

    /**
     * Check whether an order has delivery details.
     */
    protected function hasDelivery(Order $order): bool
    {
        return filled($order->shipping_line_1)
            && filled($order->shipping_city)
            && filled($order->shipping_postal_code)
            && filled($order->shipping_country);
    }
}
