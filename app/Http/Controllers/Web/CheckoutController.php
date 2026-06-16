<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\DiscountCodeService;
use App\Services\OrderCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use stdClass;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderCheckoutService $orderCheckoutService,
        protected DiscountCodeService $discountCodeService
    ) {}

    /**
     * Summary of product
     *
     * @param  mixed  $slug
     * @return \Illuminate\Contracts\View\View
     */
    public function product(?string $slug = null): View
    {
        $query = Product::where('is_active', true);
        if ($slug) {
            $query->where('slug', $slug);
        }

        $product = $query->orderBy('id')->firstOrFail();

        return view('checkout.product', [
            'product' => $product,
        ]);
    }

    /**
     * start the checkout process for a product or products
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catalog_item_uid' => ['nullable', 'uuid', 'exists:catalog_items,uid'],
            'catalog_item_uids' => ['nullable', 'array', 'min:1'],
            'catalog_item_uids.*' => ['uuid', 'exists:catalog_items,uid'],
            'catalog_item_sku' => ['nullable', 'string', 'exists:catalog_items,sku'],
            'catalog_item_skus' => ['nullable', 'array', 'min:1'],
            'catalog_item_skus.*' => ['string', 'exists:catalog_items,sku'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $catalogItemUids = array_values(array_filter((array) ($validated['catalog_item_uids'] ?? [])));
        $catalogItemSkus = array_values(array_filter((array) ($validated['catalog_item_skus'] ?? [])));

        if (empty($catalogItemUids) && filled($validated['catalog_item_uid'] ?? null)) {
            $catalogItemUids = [(string) $validated['catalog_item_uid']];
        }

        if (empty($catalogItemSkus) && filled($validated['catalog_item_sku'] ?? null)) {
            $catalogItemSkus = [(string) $validated['catalog_item_sku']];
        }

        if (empty($catalogItemUids) && ! empty($catalogItemSkus)) {
            $catalogItemsBySku = CatalogItem::whereIn('sku', $catalogItemSkus)
                ->get()
                ->keyBy('sku');

            $catalogItemUids = collect($catalogItemSkus)
                ->map(fn (string $catalogItemSku) => $catalogItemsBySku->get($catalogItemSku)?->uid)
                ->filter()
                ->values()
                ->all();
        }

        if (empty($catalogItemUids)) {
            return redirect()->route('checkout.index');
        }

        $catalogItems = CatalogItem::query()
            ->with('sellable')
            ->whereIn('uid', $catalogItemUids)
            ->where('is_published', true)
            ->get()
            ->keyBy('uid');

        $selectedCatalogItems = collect($catalogItemUids)
            ->map(fn (string $catalogItemUid) => $catalogItems->get($catalogItemUid))
            ->filter()
            ->values();

        if ($selectedCatalogItems->count() !== count($catalogItemUids) || $selectedCatalogItems->isEmpty()) {
            return redirect()->route('checkout.index');
        }

        $primaryCatalogItem = $selectedCatalogItems->first();
        $singleQuantity = (int) ($validated['quantity'] ?? 1);
        $lineItems = $this->buildCatalogLineItems($selectedCatalogItems->all(), $singleQuantity);

        $draft = [
            'catalog_item_uid' => $primaryCatalogItem->uid,
            'catalog_item_uids' => $catalogItemUids,
            'line_items' => $lineItems,
            'quantity' => array_sum(array_map(static fn (array $item) => (int) $item['quantity'], $lineItems)),
            'customer_name' => '',
            'customer_email' => '',
            'shipping_line_1' => '',
            'shipping_line_2' => '',
            'shipping_city' => '',
            'shipping_state' => '',
            'shipping_postal_code' => '',
            'shipping_country' => 'GB',
            'discount_code' => '',
        ];

        $request->session()->put('checkout_draft', $draft);
        $request->session()->forget('checkout_edit_order_uid');

        return redirect()->route('checkout.details');
    }

    /**
     * Continue checkout for an existing order
     */
    public function checkout(string $orderUid): View|RedirectResponse
    {
        $order = $this->findOrder($orderUid);

        if (! $order) {
            return redirect('/');
        }

        return redirect()->route('checkout.payment', ['orderUid' => $order->uid]);
    }

    /**
     * Show the checkout details page
     */
    public function details(Request $request): View|RedirectResponse
    {
        $draft = $this->getDraft($request);
        if (! $draft) {
            return redirect()->route('checkout.index');
        }

        $catalogItem = $this->findPrimaryCatalogItemFromDraft($draft);
        if (! $catalogItem) {
            $request->session()->forget('checkout_draft');

            return redirect()->route('checkout.index');
        }

        $order = $this->buildDraftOrderViewModel($catalogItem, $draft, $draft['line_items'] ?? []);

        return view('checkout.details', [
            'order' => $order,
        ]);
    }

    /**
     * Store the checkout details and continue to payment
     */
    public function storeDetails(Request $request): RedirectResponse
    {
        $formAction = (string) $request->input('form_action', 'continue');
        $draft = $this->getDraft($request);
        if (! $draft) {
            return redirect()->route('checkout.index');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'shipping_line_1' => ['required', 'string', 'max:255'],
            'shipping_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_state' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:32'],
            'shipping_country' => ['required', 'string', 'size:2', Rule::in(['GB'])],
            'discount_code' => ['nullable', 'string', 'max:64'],
        ]);

        $draft = array_merge($draft, $validated);
        $request->session()->put('checkout_draft', $draft);

        if (! $this->findPrimaryCatalogItemFromDraft($draft)) {
            $request->session()->forget('checkout_draft');

            return redirect()->route('checkout.index');
        }

        $editingOrderUid = $request->session()->get('checkout_edit_order_uid');
        $order = $editingOrderUid ? $this->findOrder((string) $editingOrderUid) : null;

        if (! $order) {
            $order = $this->orderCheckoutService->createPendingOrder(
                $draft['line_items'] ?? []
            );
        }

        $order = $this->orderCheckoutService->hydrateCustomerDetails($order, $validated);

        try {
            $order = $this->discountCodeService->applyPreview(
                $order,
                $validated['discount_code'] ?? null,
                $validated['customer_email'] ?? null,
                null,
                true
            );
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        $request->session()->put('checkout_draft', array_merge($draft, [
            'discount_code' => $order->discount_snapshot['code'] ?? null,
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'discount_snapshot' => $order->discount_snapshot,
        ]));
        $request->session()->put('checkout_edit_order_uid', $order->uid);

        if ($formAction === 'apply_discount') {
            return redirect()->route('checkout.details')
                ->with('checkout_status', filled($validated['discount_code'] ?? null)
                    ? 'Discount code applied.'
                    : 'Discount code removed.');
        }

        $request->session()->forget('checkout_draft');
        $request->session()->forget('checkout_edit_order_uid');

        return redirect()->route('checkout.payment', ['orderUid' => $order->uid]);
    }

    /**
     * Show the checkout payment page
     */
    public function payment(string $orderUid): View|RedirectResponse
    {
        $order = $this->findOrder($orderUid);

        if (! $order) {
            return redirect('/');
        }

        if (! $this->hasDetails($order)) {
            return redirect()->route('checkout.details');
        }

        if (! $this->hasDelivery($order)) {
            return redirect()->route('checkout.details');
        }

        request()->session()->put('checkout_draft', [
            'catalog_item_uid' => $order->catalog_item_id,
            'catalog_item_uids' => $order->items->pluck('catalog_item_id')->filter()->values()->all(),
            'line_items' => $order->items->map(function ($item) {
                return [
                    'catalog_item_uid' => $item->catalog_item_id,
                    'name_snapshot' => $item->name_snapshot,
                    'sku_snapshot' => $item->sku_snapshot,
                    'unit_amount' => $item->unit_amount,
                    'quantity' => $item->quantity,
                    'line_amount' => $item->line_amount,
                ];
            })->values()->all(),
            'quantity' => $order->quantity,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'shipping_line_1' => $order->shipping_line_1,
            'shipping_line_2' => $order->shipping_line_2,
            'shipping_city' => $order->shipping_city,
            'shipping_state' => $order->shipping_state,
            'shipping_postal_code' => $order->shipping_postal_code,
            'shipping_country' => $order->shipping_country,
            'discount_code' => $order->discount_snapshot['code'] ?? null,
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'discount_snapshot' => $order->discount_snapshot,
        ]);
        request()->session()->put('checkout_edit_order_uid', $order->uid);

        return view('checkout.payment', [
            'order' => $order,
            'stripePublishableKey' => (string) config('services.stripe.key'),
        ]);
    }

    /**
     * Show the checkout success page
     */
    public function success(string $orderUid): View|RedirectResponse
    {
        $order = Order::with(['product', 'catalogItem', 'items.catalogItem'])
            ->where('uid', $orderUid)
            ->first();

        if (! $order) {
            return redirect('/');
        }

        return view('checkout.success', [
            'order' => $order,
        ]);
    }

    /**
     * Summary of getDraft
     */
    protected function getDraft(Request $request): ?array
    {
        $draft = $request->session()->get('checkout_draft');

        return is_array($draft) ? $draft : null;
    }

    /**
     * Summary of findProduct
     */
    protected function findPrimaryCatalogItemFromDraft(array $draft): ?CatalogItem
    {
        $catalogItemUid = (string) ($draft['catalog_item_uid'] ?? '');

        if ($catalogItemUid === '') {
            $catalogItemUid = (string) collect((array) ($draft['catalog_item_uids'] ?? []))
                ->filter()
                ->first();
        }

        if ($catalogItemUid === '') {
            return null;
        }

        return CatalogItem::with('sellable')
            ->where('uid', $catalogItemUid)
            ->where('is_published', true)
            ->first();
    }

    /**
     * Summary of buildDraftOrderViewModel
     */
    protected function buildDraftOrderViewModel(CatalogItem $catalogItem, array $draft, array $lineItems = []): stdClass
    {
        $product = $catalogItem->sellable instanceof Product ? $catalogItem->sellable : null;
        $promotionPercentage = $catalogItem && $catalogItem->promotion_eligible
            ? max(0, min(100, (int) $catalogItem->promotion_percentage))
            : 0;
        $originalUnitAmount = (int) ($catalogItem?->price_amount ?? 0);
        $discountedUnitAmount = $promotionPercentage > 0
            ? (int) floor($originalUnitAmount * ((100 - $promotionPercentage) / 100))
            : $originalUnitAmount;

        $lineItems = $lineItems ?: [[
            'catalog_item_uid' => $catalogItem?->uid,
            'name_snapshot' => $catalogItem->title,
            'sku_snapshot' => $catalogItem?->sku,
            'description' => $product?->short_description,
            'unit_amount' => $discountedUnitAmount,
            'original_unit_amount' => $originalUnitAmount,
            'promotion_percentage' => $promotionPercentage,
            'quantity' => 1,
            'line_amount' => $discountedUnitAmount,
            'original_line_amount' => $originalUnitAmount,
            'currency' => strtolower((string) ($catalogItem?->currency ?? 'gbp')),
            'image_url' => $catalogItem?->image_url ?? $product?->image_url,
        ]];

        $quantity = max(1, array_sum(array_map(static fn (array $item) => (int) ($item['quantity'] ?? 1), $lineItems)));
        $amount = array_reduce($lineItems, static function (int $total, array $item): int {
            return $total + (int) ($item['line_amount'] ?? 0);
        }, 0);

        return (object) [
            'uid' => null,
            'product_id' => $product?->uid,
            'product' => $product,
            'catalog_item' => $catalogItem,
            'line_items' => $lineItems,
            'quantity' => $quantity,
            'amount' => $amount,
            'subtotal_amount' => (int) ($draft['subtotal_amount'] ?? $amount),
            'discount_amount' => (int) ($draft['discount_amount'] ?? 0),
            'total_amount' => (int) ($draft['total_amount'] ?? $amount),
            'currency' => strtolower((string) ($catalogItem?->currency ?? 'gbp')),
            'customer_name' => $draft['customer_name'] ?? null,
            'customer_email' => $draft['customer_email'] ?? null,
            'shipping_line_1' => $draft['shipping_line_1'] ?? null,
            'shipping_line_2' => $draft['shipping_line_2'] ?? null,
            'shipping_city' => $draft['shipping_city'] ?? null,
            'shipping_state' => $draft['shipping_state'] ?? null,
            'shipping_postal_code' => $draft['shipping_postal_code'] ?? null,
            'shipping_country' => $draft['shipping_country'] ?? 'GB',
            'discount_snapshot' => $draft['discount_snapshot'] ?? null,
            'status' => 'draft',
        ];
    }

    /**
     * Build line items from selected catalog items.
     */
    protected function buildCatalogLineItems(array $catalogItems, int $singleQuantity = 1): array
    {
        $singleQuantity = count($catalogItems) === 1 ? max(1, $singleQuantity) : 1;

        return array_map(static function (CatalogItem $catalogItem, int $index) use ($singleQuantity): array {
            $quantity = $index === 0 ? $singleQuantity : 1;
            $promotionPercentage = $catalogItem->promotion_eligible
                ? max(0, min(100, (int) $catalogItem->promotion_percentage))
                : 0;
            $originalUnitAmount = (int) $catalogItem->price_amount;
            $discountedUnitAmount = $promotionPercentage > 0
                ? (int) floor($originalUnitAmount * ((100 - $promotionPercentage) / 100))
                : $originalUnitAmount;

            return [
                'catalog_item_uid' => $catalogItem->uid,
                'name_snapshot' => $catalogItem->title,
                'sku_snapshot' => $catalogItem->sku,
                'description' => $catalogItem->sellable instanceof Product
                    ? ($catalogItem->sellable->short_description ?: $catalogItem->sellable->description)
                    : null,
                'unit_amount' => $discountedUnitAmount,
                'original_unit_amount' => $originalUnitAmount,
                'promotion_percentage' => $promotionPercentage,
                'quantity' => $quantity,
                'line_amount' => $discountedUnitAmount * $quantity,
                'original_line_amount' => $originalUnitAmount * $quantity,
                'currency' => $catalogItem->currency,
                'image_url' => $catalogItem->image_url,
            ];
        }, $catalogItems, array_keys($catalogItems));
    }

    /**
     * Summary of hasDraftDetails
     */
    protected function hasDraftDetails(array $draft): bool
    {
        return filled($draft['customer_name'] ?? null) && filled($draft['customer_email'] ?? null);
    }

    /**
     * Summary of hasDraftDelivery
     *
     * @param  array  $draft
     * @return bool
     */
    protected function findOrder(string $orderUid): ?Order
    {
        return Order::with(['product', 'catalogItem', 'items.catalogItem'])
            ->where('uid', $orderUid)
            ->first();
    }

    /**
     * Summary of hasDraftDelivery
     *
     * @param  array  $draft
     */
    protected function hasDetails(Order $order): bool
    {
        return filled($order->customer_name) && filled($order->customer_email);
    }

    /**
     * Summary of hasDraftDelivery
     *
     * @param  array  $draft
     */
    protected function hasDelivery(Order $order): bool
    {
        return filled($order->shipping_line_1)
            && filled($order->shipping_city)
            && filled($order->shipping_postal_code)
            && filled($order->shipping_country);
    }
}
