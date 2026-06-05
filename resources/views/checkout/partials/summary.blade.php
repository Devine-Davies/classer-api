<aside class="lg:col-span-2 space-y-4">
    @if (filled($order->customer_name) || filled($order->customer_email))
        <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 h-fit">
            <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Contact</p>
            <div class="mt-3 space-y-1 text-sm text-slate-700">
                @if (filled($order->customer_name))
                    <p>{{ $order->customer_name }}</p>
                @endif
                @if (filled($order->customer_email))
                    <p>{{ $order->customer_email }}</p>
                @endif
            </div>
        </section>
    @endif

    @if (filled($order->shipping_line_1))
        <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 h-fit">
            <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Delivery</p>
            <div class="mt-3 space-y-1 text-sm text-slate-700">
                <p>{{ $order->shipping_line_1 }}</p>
                @if (filled($order->shipping_line_2))
                    <p>{{ $order->shipping_line_2 }}</p>
                @endif
                <p>{{ $order->shipping_city }}{{ filled($order->shipping_state) ? ', ' . $order->shipping_state : '' }}</p>
                <p>{{ $order->shipping_postal_code }}</p>
                <p>{{ strtoupper($order->shipping_country) }}</p>
            </div>
        </section>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 h-fit">
        <p class="text-lg font-semibold text-slate-900">Order summary</p>
        @php
            $lineItems = [];

            if (isset($order->items) && $order->items instanceof \Illuminate\Support\Collection) {
                $lineItems = $order->items->map(function ($item) {
                    return [
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'line_amount' => $item->line_amount,
                    ];
                })->values()->all();
            } elseif (!empty($order->line_items) && is_array($order->line_items)) {
                $lineItems = array_map(static function (array $item): array {
                    return [
                        'name' => $item['product_name'] ?? 'Product',
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'line_amount' => (int) ($item['line_amount'] ?? 0),
                    ];
                }, $order->line_items);
            }
        @endphp

        <div class="mt-2 space-y-3 text-sm text-slate-700">
            @if (!empty($lineItems) && count($lineItems) > 1)
                <div class="space-y-2">
                    <ul class="space-y-1 pl-4 text-sm text-slate-600">
                        @foreach ($lineItems as $item)
                            <li class="list-disc">{{ $item['name'] ?? 'Product' }} x{{ $item['quantity'] ?? 1 }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="flex items-start justify-between gap-3">
                    <span>Quantity</span>
                    <span>x{{ $order->quantity }}</span>
                </div>
            @endif
            <div class="flex items-start justify-between gap-3">
                <span>Currency</span>
                <span>{{ strtoupper($order->currency) }}</span>
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                <span class="font-medium">Total</span>
                <span class="text-base font-semibold text-slate-900">
                    {{ strtoupper($order->currency) }} {{ number_format($order->amount / 100, 2) }}
                </span>
            </div>
        </div>
    </section>
</aside>