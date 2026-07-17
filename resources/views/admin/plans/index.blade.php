@extends('admin.layout')

@php
    $activeSection = 'plans';
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
              id="plans-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="plans-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="plans-search" name="q" type="search" placeholder="Search by code, title, or slug"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._plansSearchTimer); window._plansSearchTimer = setTimeout(() => document.getElementById('plans-filter-form').submit(), 300)">
                </label>
            </div>

            <a href="{{ url('/admin/plans/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold">
                Add plan
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
                        <th class="{{ $thClass }}">Slug</th>
                        <th class="{{ $thClass }}">Type</th>
                        <th class="{{ $thClass }}">Duration</th>
                        <th class="{{ $thClass }}">Quota</th>
                        <th class="{{ $thClass }}">SKU</th>
                        <th class="{{ $thClass }}">Price</th>
                        <th class="{{ $thClass }}">Published</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $plan)
                        @php
                            $planUid = data_get($plan, 'uid', '-');
                            $planTitle = data_get($plan, 'title', '-');
                            $planType = ucfirst((string) data_get($plan, 'type', 'unknown'));
                            $planDuration = data_get($plan, 'nice_duration', '-');
                            $planQuota = data_get($plan, 'nice_quota', '-');
                            $catalogSlug = data_get($plan, 'catalogItem.slug');
                            $catalogSku = data_get($plan, 'catalogItem.sku');
                            $catalogPrice = data_get($plan, 'catalogItem.priceAmountFormatted', '-');
                            $isPublished = (bool) data_get($plan, 'catalogItem.isPublished', false);
                        @endphp
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ url('/admin/plans/' . urlencode($planUid)) }}">
                                    <span class="orders-code">{{ $planTitle }}</span>
                                </a>

                                <div class="mt-1 text-[0.74rem] text-slate-500">UID: {{ $planUid }}</div>
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
                                <div class="font-semibold text-[#1f2d39]">{{ $planType }}</div>
                                <div class="mt-1 text-[0.74rem] text-slate-500">SKU: {{ $catalogSku ?? '-' }}</div>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <div class="font-semibold text-[#1f2d39]">{{ $planDuration }}</div>
                                <div class="mt-1 text-[0.74rem] text-slate-500">Billing duration</div>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <div class="font-semibold text-[#1f2d39]">{{ $planQuota }}</div>
                                <div class="mt-1 text-[0.74rem] text-slate-500">Storage quota</div>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <div class="font-semibold text-[#1f2d39]">{{ $catalogSku ?? '-' }}</div>
                                <div class="mt-1 text-[0.74rem] text-slate-500">Catalog SKU</div>
                            </td>

                            <td class="{{ $tdClass }}">
                                <div class="font-bold text-[#1f2d39]">{{ $catalogPrice }}</div>

                                @if ($catalogSlug)
                                    <div class="mt-1 text-[0.74rem] text-slate-500">Slug: {{ $catalogSlug }}</div>
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
                            <td colspan="8" class="orders-empty">No plans match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Plans pagination',
                'baseQuery'   => array_filter([
                    'q' => $q ?: null,
                ]),
            ])
        @endif
    </section>
@endsection
