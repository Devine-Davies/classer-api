@props([
    'stickyProducts' => [],
    'shippingText' => 'Expected shipping date: August 2026',
    'refundText' => 'Cancel anytime before dispatch for a full refund',
    'ctaUrl' => url('/checkout'),
    'ctaText' => 'Buy Now',
])

<div class="fixed inset-x-0 bottom-0 z-50 border-t border-[#eef0ed] bg-[#fafafa]/95 px-4 py-4 shadow-[0_-8px_24px_rgba(0,0,0,0.04)] backdrop-blur sm:px-6 lg:px-8 bg-[#f7f3ee]">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0 hidden lg:block">
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center md:gap-8 w-full justify-between">
            <div class="flex gap-4 overflow-x-auto no-scrollbar sm:overflow-visible">
                @foreach ($stickyProducts as $item)
                    <div class="flex min-w-max items-center gap-3">
                        <div class="flex h-12 w-16 shrink-0 items-center justify-center rounded-xl bg-[#F6F4F1]">
                            <img
                                src="{{ $item['image'] }}"
                                alt="{{ $item['title'] }}"
                                class="max-h-8 max-w-12 object-contain"
                            >
                        </div>

                        <div>
                            <p class="text-sm font-bold leading-tight text-[#073f4d]">
                                {{ $item['title'] }}
                            </p>
                            <p class="text-sm font-bold leading-tight text-[#008033]">
                                {{ $item['price'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            @include('partials.catalog-item-purchase-form', [
                'buttonLabel' => 'Order now',
                'formClass' => '',
                'catalogItemSkus' => $catalogItemSkus,
            ])
        </div>
    </div>
</div>
