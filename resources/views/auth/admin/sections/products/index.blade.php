@extends('auth.admin.layout')

@php
    $activeSection = 'products';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
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

            <a href="{{ url('/auth/admin/products/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold text-white">
                Add product
            </a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Published</th>
                        <th class="{{ $thClass }}">Price</th>
                        <th class="{{ $thClass }}">Title</th>
                        <th class="{{ $thClass }}">SKU</th>
                    </tr>
               </thead>
                <tbody>
                    @forelse ($data as $product)
                        <tr>
                           <td class="{{ $tdClass }}">
                                @if ($product->catalogItem->isPublished)
                                    <span class="users-pill bg-green-100 text-green-700">
                                        Published
                                    </span>
                                @else
                                    <span class="users-pill bg-slate-100 text-slate-600">
                                        Unpublished
                                    </span>
                                @endif
                            </td>

                            <td class="{{ $tdClass }}">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $product->catalogItem->priceAmountFormatted ?? '-' }}
                                </span>
                            </td>

                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ url('/auth/admin/products/' . urlencode($product->uid)) }}">
                                    <span class="orders-code">{{ $product->title ?? '-' }}</span>
                                </a>
                            </td>

                            <td class="{{ $tdClass }}">{{ $product->catalogItem->sku ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="orders-empty">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
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
