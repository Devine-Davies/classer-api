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

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0] whitespace-nowrap';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
    $badgeBaseClass = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold whitespace-nowrap';
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
                                {{ ucfirst(str_replace('_', ' ', $option)) }}
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
            <table class="w-full border-collapse min-w-[980px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Order</th>
                        <th class="{{ $thClass }}">Customer</th>
                        <th class="{{ $thClass }}">Items</th>
                        <th class="{{ $thClass }}">Total</th>
                        <th class="{{ $thClass }}">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $order)
                        @php
                            $items = collect(data_get($order, 'items', []));
                            $firstItem = $items->first();

                            $orderUid = data_get($order, 'uid', '-');

                            $statusClass = data_get(
                                $order,
                                'statusClass',
                                'bg-slate-50 text-slate-700 border-slate-200'
                            );

                            $statusLabel = data_get(
                                $order,
                                'statusLabel',
                                ucfirst(str_replace('_', ' ', (string) data_get($order, 'status', '-')))
                            );

                            $itemTitle = data_get($firstItem, 'displayName')
                                ?? data_get($order, 'catalogItem.displayName')
                                ?? data_get($order, 'catalogItem.title')
                                ?? data_get($order, 'product.displayName')
                                ?? data_get($order, 'product.name')
                                ?? '-';

                            $itemSku = data_get($firstItem, 'displaySku')
                                ?? data_get($order, 'catalogItem.sku');

                            $itemsCount = $items->count();
                        @endphp

                        <tr>
                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <span class="{{ $badgeBaseClass }} {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                @if (data_get($order, 'paidAtFormatted') && data_get($order, 'paidAtFormatted') !== '-')
                                    <div class="mt-1 text-[0.74rem] text-slate-500">
                                        Paid: {{ data_get($order, 'paidAtFormatted') }}
                                    </div>
                                @endif
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <a class="orders-link"
                                   href="/auth/admin/orders/{{ urlencode($orderUid) }}">
                                    <span class="orders-code">{{ $orderUid }}</span>
                                </a>
                            </td>

                            <td class="{{ $tdClass }}">
                                <div class="font-semibold text-[#1f2d39]">
                                    {{ data_get($order, 'customerName', '-') }}
                                </div>

                                <div class="mt-1 text-[0.76rem] text-slate-500">
                                    {{ data_get($order, 'customerEmail', '-') }}
                                </div>
                            </td>

                            <td class="{{ $tdClass }}">
                                <div class="font-semibold text-[#1f2d39]">
                                    {{ $itemTitle }}
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-[0.74rem] text-slate-500">
                                    @if ($itemSku)
                                        <span>SKU: {{ $itemSku }}</span>
                                    @endif

                                    @if ($itemsCount > 1)
                                        <span>{{ $itemsCount }} line items</span>
                                    @else
                                        <span>{{ number_format((int) data_get($order, 'quantity', 0)) }} item</span>
                                    @endif
                                </div>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <div class="font-bold text-[#1f2d39]">
                                    {{ data_get($order, 'totalAmountFormatted', '-') }}
                                </div>

                                @if ((int) data_get($order, 'discountAmount', 0) > 0)
                                    <div class="mt-1 text-[0.74rem] text-emerald-700">
                                        Discount: {{ data_get($order, 'discountAmountFormatted', '-') }}
                                    </div>
                                @else
                                    <div class="mt-1 text-[0.74rem] text-slate-500">
                                        Subtotal: {{ data_get($order, 'subtotalAmountFormatted', '-') }}
                                    </div>
                                @endif
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                {{ data_get($order, 'createdAtFormatted', '-') }}
                            </td>
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