<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use stdClass;

class CheckoutController extends Controller
{
    public function __construct(protected OrderCheckoutService $orderCheckoutService)
    {
    }

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

    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_uid' => ['nullable', 'uuid', 'exists:products,uid'],
            'product_uids' => ['nullable', 'array', 'min:1'],
            'product_uids.*' => ['uuid', 'exists:products,uid'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $productUids = array_values(array_filter((array) ($validated['product_uids'] ?? [])));

        if (empty($productUids) && filled($validated['product_uid'] ?? null)) {
            $productUids = [(string) $validated['product_uid']];
        }

        $products = Product::whereIn('uid', $productUids)
            ->where('is_active', true)
            ->get()
            ->keyBy('uid');

        $selectedProducts = collect($productUids)
            ->map(fn (string $productUid) => $products->get($productUid))
            ->filter()
            ->values();

        if ($selectedProducts->count() !== count($productUids)) {
            return redirect()->route('checkout.index');
        }

        if ($selectedProducts->isEmpty()) {
            return redirect()->route('checkout.index');
        }

        $primaryProduct = $selectedProducts->first();
        $singleQuantity = (int) ($validated['quantity'] ?? 1);
        $lineItems = $this->buildLineItems($selectedProducts->all(), $singleQuantity);

        $draft = [
            'product_uid' => $primaryProduct->uid,
            'product_uids' => $productUids,
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
        ];

        $request->session()->put('checkout_draft', $draft);
        $request->session()->forget('checkout_edit_order_uid');

        return redirect()->route('checkout.details');
    }

    public function checkout(string $orderUid): View|RedirectResponse
    {
        $order = $this->findOrder($orderUid);

        if (!$order) {
            return redirect('/');
        }

        return redirect()->route('checkout.payment', ['orderUid' => $order->uid]);
    }

    public function details(Request $request): View|RedirectResponse
    {
        $draft = $this->getDraft($request);
        if (!$draft) {
            return redirect()->route('checkout.index');
        }

        $product = $this->findProduct($draft['product_uid'] ?? null);
        if (!$product) {
            $request->session()->forget('checkout_draft');
            return redirect()->route('checkout.index');
        }

        $order = $this->buildDraftOrderViewModel($product, $draft, $draft['line_items'] ?? []);

        return view('checkout.details', [
            'order' => $order,
            'step' => 'details',
        ]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        $draft = $this->getDraft($request);
        if (!$draft) {
            return redirect()->route('checkout.index');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
        ]);

        $draft = array_merge($draft, $validated);
        $request->session()->put('checkout_draft', $draft);

        return redirect()->route('checkout.delivery');
    }

    public function delivery(Request $request): View|RedirectResponse
    {
        $draft = $this->getDraft($request);
        if (!$draft) {
            return redirect()->route('checkout.index');
        }

        $product = $this->findProduct($draft['product_uid'] ?? null);
        if (!$product) {
            $request->session()->forget('checkout_draft');
            return redirect()->route('checkout.index');
        }

        if (!$this->hasDraftDetails($draft)) {
            return redirect()->route('checkout.details');
        }

        $order = $this->buildDraftOrderViewModel($product, $draft);

        return view('checkout.delivery', [
            'order' => $order,
            'step' => 'delivery',
        ]);
    }

    public function storeDelivery(Request $request): RedirectResponse
    {
        $draft = $this->getDraft($request);
        if (!$draft) {
            return redirect()->route('checkout.index');
        }

        if (!$this->hasDraftDetails($draft)) {
            return redirect()->route('checkout.details');
        }

        $validated = $request->validate([
            'shipping_line_1' => ['required', 'string', 'max:255'],
            'shipping_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_state' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:32'],
            'shipping_country' => ['required', 'string', 'size:2', Rule::in(['GB'])],
        ]);

        $draft = array_merge($draft, $validated);
        $product = $this->findProduct($draft['product_uid'] ?? null);
        if (!$product) {
            $request->session()->forget('checkout_draft');
            return redirect()->route('checkout.index');
        }

        $editingOrderUid = $request->session()->get('checkout_edit_order_uid');
        $order = $editingOrderUid ? $this->findOrder((string) $editingOrderUid) : null;

        if (!$order) {
            $order = $this->orderCheckoutService->createPendingOrder(
                $draft['line_items'] ?? []
            );
        }

        $this->orderCheckoutService->hydrateCustomerDetails($order, $draft);

        $request->session()->forget('checkout_draft');
        $request->session()->forget('checkout_edit_order_uid');

        return redirect()->route('checkout.payment', ['orderUid' => $order->uid]);
    }

    public function payment(string $orderUid): View|RedirectResponse
    {
        $order = $this->findOrder($orderUid);

        if (!$order) {
            return redirect('/');
        }

        if (!$this->hasDetails($order)) {
            return redirect()->route('checkout.details');
        }

        if (!$this->hasDelivery($order)) {
            return redirect()->route('checkout.delivery');
        }

        request()->session()->put('checkout_draft', [
            'product_uid' => $order->product_id,
            'product_uids' => $order->items->pluck('product_id')->filter()->values()->all(),
            'line_items' => $order->items->map(function ($item) {
                return [
                    'product_uid' => $item->product_id,
                    'product_name' => $item->product_name,
                    'purchase_type' => $item->purchase_type,
                    'unit_amount' => $item->unit_amount,
                    'quantity' => $item->quantity,
                    'line_amount' => $item->line_amount,
                    'currency' => $item->currency,
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
        ]);
        request()->session()->put('checkout_edit_order_uid', $order->uid);

        return view('checkout.payment', [
            'order' => $order,
            'step' => 'payment',
            'stripePublishableKey' => (string) config('services.stripe.key'),
        ]);
    }

    public function success(string $orderUid): View|RedirectResponse
    {
        $order = Order::with(['product', 'items.product'])
            ->where('uid', $orderUid)
            ->first();

        if (!$order) {
            return redirect('/');
        }

        return view('checkout.success', [
            'order' => $order,
        ]);
    }

    protected function getDraft(Request $request): ?array
    {
        $draft = $request->session()->get('checkout_draft');
        return is_array($draft) ? $draft : null;
    }

    protected function findProduct(?string $productUid): ?Product
    {
        if (!$productUid) {
            return null;
        }

        return Product::where('uid', $productUid)
            ->where('is_active', true)
            ->first();
    }

    protected function buildDraftOrderViewModel(Product $product, array $draft, array $lineItems = []): stdClass
    {
        $lineItems = $lineItems ?: [[
            'product_uid' => $product->uid,
            'product_name' => $product->name,
            'purchase_type' => $product->purchase_type,
            'unit_amount' => $product->price_amount,
            'quantity' => 1,
            'line_amount' => $product->price_amount,
            'currency' => $product->currency,
        ]];

        $quantity = max(1, array_sum(array_map(static fn (array $item) => (int) ($item['quantity'] ?? 1), $lineItems)));
        $amount = array_reduce($lineItems, static function (int $total, array $item): int {
            return $total + (int) ($item['line_amount'] ?? 0);
        }, 0);

        return (object) [
            'uid' => null,
            'product_id' => $product->uid,
            'product' => $product,
            'line_items' => $lineItems,
            'quantity' => $quantity,
            'amount' => $amount,
            'currency' => strtolower($product->currency),
            'customer_name' => $draft['customer_name'] ?? null,
            'customer_email' => $draft['customer_email'] ?? null,
            'shipping_line_1' => $draft['shipping_line_1'] ?? null,
            'shipping_line_2' => $draft['shipping_line_2'] ?? null,
            'shipping_city' => $draft['shipping_city'] ?? null,
            'shipping_state' => $draft['shipping_state'] ?? null,
            'shipping_postal_code' => $draft['shipping_postal_code'] ?? null,
            'shipping_country' => $draft['shipping_country'] ?? 'GB',
            'status' => 'draft',
        ];
    }

    protected function buildLineItems(array $products, int $singleQuantity = 1): array
    {
        $singleQuantity = count($products) === 1 ? max(1, $singleQuantity) : 1;

        return array_map(static function (Product $product, int $index) use ($singleQuantity): array {
            $quantity = $index === 0 ? $singleQuantity : 1;

            return [
                'product_uid' => $product->uid,
                'product_name' => $product->name,
                'purchase_type' => $product->purchase_type,
                'unit_amount' => $product->price_amount,
                'quantity' => $quantity,
                'line_amount' => $product->price_amount * $quantity,
                'currency' => $product->currency,
            ];
        }, $products, array_keys($products));
    }

    protected function hasDraftDetails(array $draft): bool
    {
        return filled($draft['customer_name'] ?? null) && filled($draft['customer_email'] ?? null);
    }

    protected function findOrder(string $orderUid): ?Order
    {
        return Order::with(['product', 'items.product'])
            ->where('uid', $orderUid)
            ->first();
    }

    protected function hasDetails(Order $order): bool
    {
        return filled($order->customer_name) && filled($order->customer_email);
    }

    protected function hasDelivery(Order $order): bool
    {
        return filled($order->shipping_line_1)
            && filled($order->shipping_city)
            && filled($order->shipping_postal_code)
            && filled($order->shipping_country);
    }
}
