<!DOCTYPE html>
<html lang="en">

@php
    $items = collect(data_get($order, 'items', []));
    $currency = strtoupper((string) data_get($order, 'currency', 'GBP'));
    $orderStatus = (string) data_get($order, 'status', 'paid');
    $isPaid = $orderStatus === 'paid';

    $formatMoney = function ($amount) use ($currency) {
        if ($amount === null || $amount === '') {
            return '-';
        }

        return new \Illuminate\Support\HtmlString(
            e($currency === 'GBP' ? '£' : $currency . ' ') . number_format(((int) $amount) / 100, 2)
        );
    };

    $shippingAddress = collect([
        data_get($order, 'shippingLine1'),
        data_get($order, 'shippingLine2'),
        collect([
            data_get($order, 'shippingCity'),
            data_get($order, 'shippingState'),
            data_get($order, 'shippingPostalCode'),
        ])->filter()->implode(', '),
        strtoupper((string) data_get($order, 'shippingCountry', '')),
    ])->filter()->implode(', ');
@endphp

<head>
    <title>Order Confirmed</title>
    @include('partials.meta')
</head>

<body class="antialiased bg-off-white text-slate-900">
    @include('partials.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-16">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="relative bg-slate-950 px-6 py-10 text-white md:px-10 md:py-14">
                <div class="absolute right-8 top-8 hidden h-28 w-28 rounded-full bg-white/10 md:block"></div>
                <div class="absolute -bottom-10 right-24 hidden h-36 w-36 rounded-full bg-white/5 md:block"></div>

                <div class="relative max-w-3xl">
                    <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-400 text-3xl text-slate-950 shadow-lg">
                        ✓
                    </div>

                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.25em] text-emerald-300">
                        Thank you for your order
                    </p>

                    <h1 class="text-3xl font-bold tracking-tight md:text-5xl">
                        Your order is confirmed.
                    </h1>

                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-200 md:text-lg">
                        We’ve received your order and we’re getting everything ready. A confirmation has been sent to
                        <span class="font-semibold text-white">{{ data_get($order, 'customerEmail', 'your email address') }}</span>.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <span class="inline-flex w-fit items-center rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800">
                            {{ $isPaid ? 'Payment received' : ucfirst($orderStatus) }}
                        </span>

                        <span class="text-sm text-slate-300">
                            Order reference:
                            <span class="font-semibold text-white">{{ data_get($order, 'uid', '-') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 px-6 py-8 md:grid-cols-[1.3fr_0.7fr] md:px-10 md:py-10">
                <div class="space-y-8">
                    <section>
                        <h2 class="text-xl font-bold text-slate-950">What happens next?</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-full bg-white font-bold text-slate-950 shadow-sm">1</div>
                                <h3 class="font-semibold text-slate-950">We check your order</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Your items and delivery details are being reviewed.</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-full bg-white font-bold text-slate-950 shadow-sm">2</div>
                                <h3 class="font-semibold text-slate-950">We prepare it</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">We’ll carefully pack everything so it’s ready to go.</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-full bg-white font-bold text-slate-950 shadow-sm">3</div>
                                <h3 class="font-semibold text-slate-950">We keep you updated</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">You’ll receive updates by email as your order progresses.</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <h2 class="text-xl font-bold text-slate-950">Items in your order</h2>
                            <span class="text-sm text-slate-500">
                                {{ number_format($items->sum(fn ($item) => (int) data_get($item, 'quantity', 0))) }} item{{ $items->sum(fn ($item) => (int) data_get($item, 'quantity', 0)) === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white">
                            @forelse ($items as $item)
                                @php
                                    $catalogItem = data_get($item, 'catalogItem');
                                    $name = data_get($item, 'nameSnapshot')
                                        ?? data_get($catalogItem, 'title')
                                        ?? 'Item';
                                    $sku = data_get($item, 'skuSnapshot')
                                        ?? data_get($catalogItem, 'sku');
                                    $quantity = (int) data_get($item, 'quantity', 1);
                                    $lineAmount = data_get($item, 'lineAmount')
                                        ?? ((int) data_get($item, 'unitAmount', 0) * max(1, $quantity));
                                    $imageUrl = data_get($catalogItem, 'imageUrl')
                                        ?? data_get($item, 'imageUrl');
                                @endphp

                                <div class="flex gap-4 p-4 sm:p-5">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-400">Item</div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-slate-950">{{ $name }}</h3>
                                        @if ($sku)
                                            <p class="mt-1 text-sm text-slate-500">SKU: {{ $sku }}</p>
                                        @endif
                                        <p class="mt-2 text-sm text-slate-600">Quantity: {{ $quantity }}</p>
                                    </div>

                                    <div class="text-right font-semibold text-slate-950">
                                        {{ $formatMoney($lineAmount) }}
                                    </div>
                                </div>
                            @empty
                                <div class="p-5 text-sm text-slate-600">
                                    Your order details are being prepared.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="space-y-5">
                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h2 class="text-lg font-bold text-slate-950">Order summary</h2>

                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-600">Subtotal</span>
                                <span class="font-medium text-slate-900">{{ $formatMoney(data_get($order, 'subtotalAmount')) }}</span>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span class="text-slate-600">Discount</span>
                                <span class="font-medium text-slate-900">{{ $formatMoney(data_get($order, 'discountAmount', 0)) }}</span>
                            </div>

                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex justify-between gap-4 text-base">
                                    <span class="font-bold text-slate-950">Total paid</span>
                                    <span class="font-bold text-slate-950">{{ $formatMoney(data_get($order, 'totalAmount')) }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h2 class="text-lg font-bold text-slate-950">Delivery details</h2>

                        <div class="mt-4 space-y-4 text-sm leading-6 text-slate-600">
                            <div>
                                <p class="font-semibold text-slate-950">{{ data_get($order, 'customerName', 'Customer') }}</p>
                                <p>{{ data_get($order, 'customerEmail', '-') }}</p>
                            </div>

                            <div>
                                <p class="font-semibold text-slate-950">Shipping address</p>
                                <p>{{ $shippingAddress ?: 'Address details are being confirmed.' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <h2 class="text-lg font-bold text-emerald-950">Need help?</h2>
                        <p class="mt-2 text-sm leading-6 text-emerald-900">
                            If anything looks wrong, reply to your confirmation email and we’ll help put it right.
                        </p>
                    </section>

                    <a href="/" class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Continue shopping
                    </a>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>