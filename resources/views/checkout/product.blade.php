<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Checkout</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    @php
        $catalogItem = $product->catalogItem;
        $promotionPercentage = max(0, min(100, (int) ($catalogItem->promotion_percentage ?? 0)));
        $originalAmount = (int) ($catalogItem->price_amount ?? 0);
        $discountedAmount = max(0, $originalAmount - (int) floor(($originalAmount * $promotionPercentage) / 100));
        $currency = strtoupper((string) ($catalogItem->currency ?? 'gbp'));
    @endphp

    <main class="mx-auto max-w-5xl px-4 py-10 md:py-14">
        <div class="grid gap-8 md:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">One-time purchase</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $product->name }}</h1>
                <p class="mt-4 text-slate-600 leading-relaxed">{{ $product->long_description ?? $product->description }}</p>

                <div class="mt-8 flex items-baseline gap-2">
                    @if ($promotionPercentage > 0)
                        <span class="text-lg text-slate-400 line-through">
                            {{ $currency }} {{ number_format($originalAmount / 100, 2) }}
                        </span>
                        <span class="text-3xl font-bold text-brand-color">
                            {{ $currency }} {{ number_format($discountedAmount / 100, 2) }}
                        </span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                            -{{ $promotionPercentage }}%
                        </span>
                    @else
                        <span class="text-3xl font-bold text-brand-color">
                            {{ $currency }} {{ number_format($originalAmount / 100, 2) }}
                        </span>
                    @endif
                    <span class="text-sm text-slate-500">inc VAT</span>
                </div>

                @include('partials.shared.catalog-item-purchase-form', [
                    'catalogItemUid' => $product->catalogItem?->uid,
                    'catalogItemSku' => $product->catalogItem?->sku,
                ])
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                <h2 class="text-xl font-semibold text-slate-900">What happens next</h2>
                <ol class="mt-4 list-decimal pl-5 space-y-2 text-slate-600">
                    <li>We save your checkout as a draft.</li>
                    <li>You enter your details and delivery address.</li>
                    <li>We create your order when you continue to payment.</li>
                    <li>You complete payment securely with Stripe.</li>
                    <li>We confirm payment with webhook verification.</li>
                </ol>
            </section>
        </div>
    </main>
</body>

</html>
