@extends('auth.admin.layout')

@php
    $activeSection = 'orders';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $statusOptions = $status_options ?? [];
    $status        = $filters['status'] ?? 'all';
    $q             = $filters['q'] ?? request('q', '');

    $statusClasses = [
        'pending'  => 'is-inactive',
        'paid'     => 'is-verified',
        'failed'   => 'is-suspended',
        'canceled' => 'is-deactivated',
    ];

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="orders-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="orders-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="orders-search" name="q" type="search" placeholder="Search order, customer, or product"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._ordersSearchTimer); window._ordersSearchTimer = setTimeout(() => document.getElementById('orders-filter-form').submit(), 300)">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="orders-status-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Status</span>
                    <select id="orders-status-filter" name="status"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('orders-filter-form').submit()">
                        <option value="all" @selected($status === 'all')>All</option>
                        @foreach ($statusOptions as $option)
                            <option value="{{ strtolower($option) }}" @selected($status === strtolower($option))>
                                {{ ucfirst($option) }}
                            </option>
                        @endforeach
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
                        <th class="{{ $thClass }}">Order UID</th>
                        <th class="{{ $thClass }}">Customer</th>
                        <th class="{{ $thClass }}">Catalog item</th>
                        <th class="{{ $thClass }}">Amount</th>
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $order)
                        @php
                            $statusLabel = strtolower($order->status ?? 'pending');
                            $statusClass = $statusClasses[$statusLabel] ?? 'is-inactive';
                        @endphp
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="/auth/admin/orders/{{ urlencode($order->uid) }}">
                                    <span class="orders-code">{{ $order->uid ?? '-' }}</span>
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">{{ $order->customer_email ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ $order->catalog_item->title ?? '-' }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($order->currency && $order->amount !== null)
                                    {{ strtoupper($order->currency) }} {{ number_format($order->amount / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="{{ $tdClass }}"><span class="orders-status {{ $statusClass }}">{{ ucfirst($statusLabel) }}</span></td>
                            <td class="{{ $tdClass }}">{{ isset($order->created_at) ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="orders-empty">No orders match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Orders pagination',
                'baseQuery'   => array_filter([
                    'q'      => $q ?: null,
                    'status' => $status !== 'all' ? $status : null,
                ]),
            ])
        @endif
    </section>
@endsection
