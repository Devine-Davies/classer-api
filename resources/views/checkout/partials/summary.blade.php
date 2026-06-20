@php
    $lineItems = [];

    if (!empty($order->line_items) && is_array($order->line_items)) {
        $lineItems = array_map(static function (array $item): array {
            return [
                'name' => $item['name_snapshot'] ?? 'Product',
                'description' => $item['description'] ?? ($item['short_description'] ?? null),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'line_amount' => (int) ($item['line_amount'] ?? 0),
                'original_line_amount' => (int) ($item['original_line_amount'] ?? ($item['line_amount'] ?? 0)),
                'promotion_percentage' => max(0, min(100, (int) ($item['promotion_percentage'] ?? 0))),
                'image_url' => $item['image_url'] ?? null,
            ];
        }, $order->line_items);
    }

    $currency = strtoupper((string) ($order->currency ?? 'GBP'));
    $currencySymbol = $currency === 'GBP' ? '£' : $currency . ' ';
    $subtotalAmount = (int) ($order->subtotal_amount ?? ($order->amount ?? 0));
    $discountAmount = (int) ($order->discount_amount ?? 0);
    $totalAmount = (int) ($order->total_amount ?? ($order->amount ?? 0));
    $originalSubtotalAmount = array_reduce(
        $lineItems,
        static function (int $carry, array $item): int {
            return $carry + (int) ($item['original_line_amount'] ?? ($item['line_amount'] ?? 0));
        },
        0,
    );
    $formatAmount = static function (int $amount) use ($currencySymbol): string {
        $value = number_format($amount / 100, 2, '.', '');

        return $currencySymbol . $value;
    };
@endphp

<aside class="lg:col-span-2 space-y-4">
    <section class="h-fit rounded-2xl bg-white p-6">
        <p class="text-xl font-semibold leading-none">Order summary</p>

        <div class="mt-4 rounded-2xl bg-white">
            <div class="space-y-4">
                @foreach ($lineItems as $item)
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border p-2 ">
                                @if (filled($item['image_url'] ?? null))
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                        class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center text-[10px] font-semibold text-slate-400">
                                        ITEM</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-base font-semibold leading-tight">{{ $item['name'] }}</p>
                                @if (filled($item['description'] ?? null))
                                    <p class="text-sm pt-1 leading-tight text-slate-400">{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </div>
                        <div
                            class="shrink-0 text-right leading-none {{ (int) $item['line_amount'] === 0 ? 'text-[#2ea85d]' : 'text-[#1a4b59]' }}">
                            @if ((int) $item['line_amount'] === 0)
                                <p class="text-base font-semibold">FREE</p>
                            @elseif (
                                (int) ($item['promotion_percentage'] ?? 0) > 0 &&
                                    (int) ($item['original_line_amount'] ?? 0) > (int) $item['line_amount']
                            )
                                <p class="text-sm text-slate-400 line-through">
                                    {{ $formatAmount((int) ($item['original_line_amount'] ?? 0)) }}</p>
                                <p class="text-base font-semibold">{{ $formatAmount((int) $item['line_amount']) }}</p>
                            @else
                                <p class="text-base font-semibold">{{ $formatAmount((int) $item['line_amount']) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="h-fit rounded-2xl bg-white p-6">
        <div class="space-y-2 text-base leading-tight text-slate-400">
            <div class="flex items-center justify-between">
                <span>Subtotal</span>
                <span>{{ $formatAmount($subtotalAmount) }}</span>
            </div>

            @if ($discountAmount > 0)
                <div class="flex font-bold items-center justify-between text-emerald-700">
                    <span>Discount code</span>
                    <span>-{{ $formatAmount($discountAmount) }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <span>Shipping</span>
                <span>-</span>
            </div>
        </div>

        <div class="mt-4 border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between text-xl font-semibold leading-none">
                <span>Total</span>
                <span>{{ $formatAmount($totalAmount) }}</span>
            </div>
        </div>
    </section>

    <div class="mt-4 flex items-center justify-center gap-2 [&_svg]:!block [&_svg]:h-8 [&_svg]:w-10"
        aria-label="Supported cards">
        <span class="inline-flex items-center">@icon('card-visa')</span>
        <span class="inline-flex items-center">@icon('card-mastercard')</span>
        <span class="inline-flex items-center">@icon('card-amex')</span>
        <span class="inline-flex items-center">@icon('card-discover')</span>
    </div>
</aside>
