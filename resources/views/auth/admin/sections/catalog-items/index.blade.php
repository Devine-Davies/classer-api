@extends('auth.admin.layout')

@php
    $activeSection = 'catalog-items';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $q            = $filters['q'] ?? request('q', '');
    $sellableType = request('sellable_type', 'all');
    $isActive     = request('is_active', 'all');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
@endphp

@section('content')
    <header class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="m-0 text-admin-ink text-xl font-bold">Catalog Items</h2>
            <p class="mt-[0.35rem] text-admin-muted">Manage sellable catalog records across products and plans.</p>
        </div>
        <a href="{{ url('/auth/admin/catalog-items/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold text-white">Add catalog item</a>
    </header>

    <section class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)]">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="catalog-items-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="catalog-items-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="catalog-items-search" name="q" type="search" placeholder="Search title, SKU, slug, UID"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._catalogItemsSearchTimer); window._catalogItemsSearchTimer = setTimeout(() => document.getElementById('catalog-items-filter-form').submit(), 300)">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="catalog-items-type-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Type</span>
                    <select id="catalog-items-type-filter" name="sellable_type"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('catalog-items-filter-form').submit()">
                        <option value="all" @selected($sellableType === 'all')>All</option>
                        <option value="App\Models\Product" @selected($sellableType === 'App\Models\Product')>Product</option>
                        <option value="App\Models\Plan" @selected($sellableType === 'App\Models\Plan')>Plan</option>
                    </select>
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="catalog-items-active-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Active</span>
                    <select id="catalog-items-active-filter" name="is_active"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('catalog-items-filter-form').submit()">
                        <option value="all" @selected($isActive === 'all')>All</option>
                        <option value="yes" @selected($isActive === 'yes')>Yes</option>
                        <option value="no" @selected($isActive === 'no')>No</option>
                    </select>
                </label>
            </div>

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
                        <th class="{{ $thClass }}">SKU</th>
                        <th class="{{ $thClass }}">Sellable</th>
                        <th class="{{ $thClass }}">Price</th>
                        <th class="{{ $thClass }}">Promo</th>
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        @php
                            $sellableType = str_replace('App\\Models\\', '', $item->sellable_type ?? '');
                        @endphp
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ url('/auth/admin/catalog-items/' . urlencode($item->uid)) }}">
                                    <span class="orders-code">{{ $item->title ?? '-' }}</span>
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">{{ $item->sku ?? '-' }}</td>
                            <td class="{{ $tdClass }}">
                                <span class="text-xs text-slate-500">{{ $sellableType }}</span>
                                @if ($item->sellable)
                                    <div class="text-sm font-medium text-slate-900">{{ $item->sellable->title ?? $item->sellable->name ?? '-' }}</div>
                                @else
                                    <div class="text-sm text-slate-500">-</div>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($item->price_amount !== null && $item->currency)
                                    {{ strtoupper($item->currency) }} {{ number_format($item->price_amount / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($item->promotion_percentage)
                                    <span class="text-sm font-medium text-slate-900">{{ $item->promotion_percentage }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="{{ $tdClass }}">{{ isset($item->updated_at) ? \Illuminate\Support\Carbon::parse($item->updated_at)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="orders-empty">No catalog items match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Catalog items pagination',
                'baseQuery'   => array_filter([
                    'q'             => $q ?: null,
                    'sellable_type' => $sellableType !== 'all' ? $sellableType : null,
                    'is_active'     => $isActive !== 'all' ? $isActive : null,
                ]),
            ])
        @endif
    </section>
@endsection
