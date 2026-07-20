@extends('admin.layout')

@php
    $activeSection = 'products';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0] whitespace-nowrap';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
    $badgeBaseClass = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold whitespace-nowrap';


@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="search" name="q" type="search" placeholder="Search by code, title, or slug"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._productsSearchTimer); window._productsSearchTimer = setTimeout(() => document.getElementById('filter-form').submit(), 300)">
                </label>
            </div>

            <a href="{{ url('/admin/products/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold">
                Add product
            </a>

            <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                @if ($total)
                    {{ $from }}&ndash;{{ $to }} of {{ number_format($total) }}
                @else
                    0 results
                @endif
            </p>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Title</th>
                        <th class="{{ $thClass }}">Price</th>
                        <th class="{{ $thClass }}">SKU</th>
                        <th class="{{ $thClass }}">Slug</th>
                        <th class="{{ $thClass }}">Published</th>
                    </tr>
               </thead>
                <tbody>
                    @forelse ($data as $product)
                        @php
                            $productUid = data_get($product, 'uid', '-');
                            $productCode = data_get($product, 'code');
                            $productTitle = data_get($product, 'title', '-');
                            $catalogSlug = data_get($product, 'catalogItem.slug');
                            $catalogSku = data_get($product, 'catalogItem.sku');
                            $catalogPrice = data_get($product, 'catalogItem.pricing.displayPriceFormatted', '-');
                            $catalogOriginalPrice = data_get($product, 'catalogItem.pricing.basePriceFormatted');
                            $catalogDiscountAmount = data_get($product, 'catalogItem.pricing.discountAmountFormatted');
                            $catalogDiscountPercentage = (int) data_get($product, 'catalogItem.pricing.promotionPercentage', 0);
                            $catalogHasDiscount = (bool) data_get($product, 'catalogItem.pricing.hasDiscount', false);
                            $isPublished = (bool) data_get($product, 'catalogItem.isPublished', false);
                        @endphp
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ url('/admin/products/' . urlencode($productUid)) }}">
                                    <span class="font-bold text-[#1f2d39]">{{ $productTitle }}</span>
                                </a>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-[0.74rem] text-slate-500">
                                    @if ($productCode)
                                        <span>Code: {{ $productCode }}</span>
                                    @endif
                                    <span>UID: {{ $productUid }}</span>
                                </div>
                            </td>

                            <td class="{{ $tdClass }}">
                                <div class="flex items-center gap-2">
                                    <div class="font-bold text-[#1f2d39]">{{ $catalogPrice }}</div>

                                    @if ($catalogHasDiscount)
                                        <div class="text-[0.8rem] font-medium text-slate-500 line-through">{{ $catalogOriginalPrice }}</div>
                                    @endif
                                </div>

                                @if ($catalogHasDiscount)
                                    <div class="mt-1 text-[0.74rem] text-[#0b7a3f]">
                                        Discount: -{{ $catalogDiscountAmount }} ({{ $catalogDiscountPercentage }}%)
                                    </div>
                                @endif
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <div>{{ $catalogSku ?? '-' }}</div>
                                <div class="mt-1 text-[0.74rem] text-slate-500">Catalog SKU</div>
                            </td>

                            <td class="{{ $tdClass }}">
                                @if ($catalogSlug)
                                    <a class="orders-link"
                                       href="{{ url('/products/' . $catalogSlug) }}">
                                        <span class="orders-code">{{ $catalogSlug }}</span>
                                    </a>

                                    <div class="mt-1 text-[0.74rem] text-slate-500">/products/{{ $catalogSlug }}</div>
                                @else
                                    <span>-</span>
                                @endif
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                @if ($isPublished)
                                    <span class="{{ $badgeBaseClass }} bg-green-100 text-green-700 border-green-200">
                                        Published
                                    </span>
                                @else
                                    <span class="{{ $badgeBaseClass }} bg-slate-100 text-slate-600 border-slate-200">
                                        Unpublished
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="orders-empty">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Products pagination',
                'baseQuery'   => array_filter([
                    'q' => $q ?: null,
                ]),
            ])
        @endif
    </section>
@endsection
