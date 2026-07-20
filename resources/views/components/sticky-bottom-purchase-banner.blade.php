@props([
    'ctaText' => 'Buy Now',
])

@php
    $catalogItemSkus = array_values(
        array_filter((array) (view()->shared('catalogItemSkus') ?? []))
    );

    $catalogItems = empty($catalogItemSkus)
        ? collect()
        : \App\Models\CatalogItem::query()
            ->whereIn('sku', $catalogItemSkus)
            ->where('is_published', true)
            ->get()
            ->keyBy('sku');

    // Preserve configured SKU order and exclude unavailable items.
    $catalogItemsBySku = collect($catalogItemSkus)
        ->mapWithKeys(
            fn (string $sku): array => [$sku => $catalogItems->get($sku)]
        )
        ->filter();

    $formatAmount = static function (int $amount, string $currency): string {
        $currency = strtoupper($currency);
        $formattedAmount = number_format($amount / 100, 2);

        return match ($currency) {
            'GBP' => '£'.$formattedAmount,
            'EUR' => '€'.$formattedAmount,
            'USD' => '$'.$formattedAmount,
            default => $currency.' '.$formattedAmount,
        };
    };
@endphp

<div
    class="
        fixed inset-x-0 bottom-0 z-50
        border-t border-white/60
        bg-[#f7f3ee]/80
        backdrop-blur-xl
        supports-[backdrop-filter]:bg-[#f7f3ee]/70
    "
>
    <div class="w-full px-4 md:px-6 px-4 py-3 sm:px-6 sm:py-4 lg:px-8" >
        <div class="mx-auto w-full max-w-7xl">
            <div
                class="
                    flex flex-col gap-3
                    md:flex-row md:items-center md:justify-between md:gap-8
                "
            >
                <div
                    class="
                        -mx-1 flex min-w-0 flex-1 gap-3
                        overflow-x-auto px-1 pb-1
                        no-scrollbar
                        md:gap-5 md:overflow-visible md:pb-0
                    "
                >
                    @foreach ($catalogItemsBySku as $catalogItemSku => $catalogItem)
                        @php
                            $imageUrl = (string) ($catalogItem->image_url ?? '');
                            $title = (string) ($catalogItem->title ?? $catalogItemSku);

                            $originalAmount = max(
                                0,
                                (int) ($catalogItem->price_amount ?? 0)
                            );

                            $promotionPercentage = max(
                                0,
                                min(
                                    100,
                                    (int) ($catalogItem->promotion_percentage ?? 0)
                                )
                            );

                            $discountedAmount = $promotionPercentage > 0
                                ? (int) floor(
                                    $originalAmount * ((100 - $promotionPercentage) / 100)
                                )
                                : $originalAmount;

                            $discountAmount = max(
                                0,
                                $originalAmount - $discountedAmount
                            );

                            $hasDiscount = $promotionPercentage > 0
                                && $discountAmount > 0;

                            $currency = (string) ($catalogItem->currency ?? 'GBP');

                            $displayPrice = $discountedAmount === 0
                                ? 'FREE'
                                : $formatAmount($discountedAmount, $currency);
                        @endphp

                    <article
                        class="
                            flex min-w-[320px] items-center gap-4
                            rounded-xl border border-white/80
                            bg-white/65 p-2
                            sm:min-w-[340px]
                            lg:min-w-[360px]
                        "
                    >
                        <div
                            class="
                                flex h-20 w-24 shrink-0 items-center justify-center
                                overflow-hidden rounded-2xl
                            "
                        >
                            @if ($imageUrl !== '')
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $title }}"
                                    class="max-h-16 max-w-16 object-contain"
                                    loading="lazy"
                                >
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="
                                    max-w-[220px] truncate
                                    text-base font-extrabold leading-tight
                                    text-[#073f4d]
                                    sm:text-lg
                                "
                                title="{{ $title }}"
                            >
                                {{ $title }}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1.5">
                                @if ($hasDiscount)
                                    <span
                                        class="
                                            text-sm font-medium text-[#708089]
                                            line-through decoration-[#708089]/60
                                        "
                                    >
                                        {{ $formatAmount($originalAmount, $currency) }}
                                    </span>
                                @endif

                                <span
                                    class="
                                        text-lg font-black leading-none
                                        text-[#008033]
                                        sm:text-xl
                                    "
                                >
                                    {{ $displayPrice }}
                                </span>

                                @if ($hasDiscount)
                                    <span
                                        class="
                                            rounded-full
                                            bg-[#008033]/12
                                            px-2.5 py-1
                                            text-[11px] font-extrabold uppercase tracking-wide
                                            text-[#08783a]
                                        "
                                    >
                                        Save {{ $promotionPercentage }}%
                                    </span>
                                @endif
                            </div>

                            @if ($hasDiscount)
                                <p
                                    class="
                                        mt-1.5
                                        text-xs font-bold leading-tight
                                        text-[#0b7a3f]
                                    "
                                >
                                    You save {{ $formatAmount($discountAmount, $currency) }}
                                </p>
                            @endif
                        </div>
                    </article>
                    @endforeach
                </div>

                <div
                    class="
                        shrink-0
                        border-t border-[#073f4d]/10 pt-3
                        md:border-l md:border-t-0 md:pl-6 md:pt-0
                    "
                >
                    @include('partials.catalog-item-purchase-form', [
                        'buttonLabel' => $ctaText,
                        'formClass' => 'w-full md:w-auto',
                        'catalogItemSkus' => $catalogItemSkus,
                    ])
                </div>
            </div>
        </div>
    </div>

    {{-- Supports devices with a bottom safe area, such as iPhones. --}}
    <div class="h-[env(safe-area-inset-bottom)]"></div>
</div>