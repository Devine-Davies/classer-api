@extends('auth.admin.layout')

@php
    $activeSection = 'plans';
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
    <header class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="m-0 text-admin-ink text-xl font-bold">Plans</h2>
            <p class="mt-[0.35rem] text-admin-muted">Manage plans linked to user subscriptions.</p>
        </div>
        <a href="{{ url('/auth/admin/plans/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold text-white">Add plan</a>
    </header>

    <section class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)]">
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
                        <th class="{{ $thClass }}">Code</th>
                        <th class="{{ $thClass }}">Title</th>
                        <th class="{{ $thClass }}">Type</th>
                        <th class="{{ $thClass }}">Duration</th>
                        <th class="{{ $thClass }}">Quota</th>
                        <th class="{{ $thClass }}">Catalog Item</th>
                        <th class="{{ $thClass }}">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $plan)
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ url('/auth/admin/plans/' . urlencode($plan->uid)) }}">
                                    <span class="orders-code">{{ $plan->code ?? '-' }}</span>
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">{{ $plan->title ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ ucfirst($plan->type ?? 'unknown') }}</td>
                            <td class="{{ $tdClass }}">{{ $plan->duration ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ number_format($plan->quota ?? 0) }} bytes</td>
                            <td class="{{ $tdClass }}">{{ $plan->catalog_item->sku ?? '-' }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($plan->catalog_item && $plan->catalog_item->price_amount !== null)
                                    {{ strtoupper($plan->catalog_item->currency ?? 'gbp') }} {{ number_format($plan->catalog_item->price_amount / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="orders-empty">No plans match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
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
