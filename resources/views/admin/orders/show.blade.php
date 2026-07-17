@extends('admin.layout')

@php
    $activeSection = 'orders';

    $order = $order ?? $entity ?? [];

    $uid = data_get($order, 'uid', $orderUid ?? '-');
    $items = collect(data_get($order, 'items', []));
    $payments = collect(data_get($order, 'payments', []));

    $statusClass = data_get($order, 'statusClass', 'bg-slate-50 text-slate-700 border-slate-200');
    $statusLabel = data_get($order, 'statusLabel', '-');

    $cardClass = 'rounded-xl border border-slate-200 bg-white shadow-sm';
    $cardHeaderClass = 'flex items-center justify-between gap-4 px-5 py-4';
    $sectionTitleClass = 'text-[1rem] font-bold text-slate-950';
    $mutedTextClass = 'text-[0.9rem] leading-6 text-slate-500';
    $badgeClass = 'inline-flex items-center rounded-md border px-2.5 py-1 text-[0.72rem] font-bold';
@endphp

@section('content')
    <div class="max-w-3xl space-y-6">
        <header class="space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="m-0 text-[1.75rem] font-bold leading-tight text-slate-950">
                    Order ID: {{ $uid }}
                </h1>

                <span class="{{ $badgeClass }} {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <p class="m-0 text-[0.95rem] text-slate-500">
                {{ data_get($order, 'createdAtFormatted', '-') }}
                <span class="text-slate-800">from <span class="text-slate-800 font-bold">{{ data_get($order, 'customerName', '-') }}</span></span>
                <span class="hidden sm:inline">({{ data_get($order, 'customerEmail', '-') }})</span>
            </p>
        </header>

        <section class="{{ $cardClass }}">
            <div class="{{ $cardHeaderClass }}">
                <h2 class="{{ $sectionTitleClass }}">Order Item</h2>
                <!-- <span class="text-2xl leading-none text-slate-400">⌄</span> -->
            </div>

            <div class="px-5 pb-5">
                <span class="{{ $badgeClass }} {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>

                <p class="mt-4 mb-6 {{ $mutedTextClass }}">
                    Review the products, plans, and catalog items included in this order.
                </p>

                <div class="space-y-5">
                    @forelse ($items as $item)
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-4">
                                @php
                                    $imageUrl = data_get($item, 'catalogItem.imageUrl')
                                        ?? data_get($item, 'product.imageUrl');
                                @endphp

                                <div class="flex h-[92px] w-[92px] shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                                    @if ($imageUrl)
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ data_get($item, 'displayName', 'Order item') }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-[0.75rem] font-semibold text-slate-400">
                                            No image
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <p class="m-0 text-[0.9rem] text-slate-500">
                                        {{ data_get($item, 'type', 'Item') }}
                                    </p>

                                    <h3 class="mt-1 truncate text-[1.05rem] font-bold text-slate-950">
                                        {{ data_get($item, 'displayName', '-') }}
                                    </h3>

                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[0.9rem] text-slate-500">
                                        @if (data_get($item, 'displaySku'))
                                            <span>{{ data_get($item, 'displaySku') }}</span>
                                        @endif

                                        @if (data_get($item, 'catalogItem.currency'))
                                            <span>{{ strtoupper(data_get($item, 'catalogItem.currency')) }}</span>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-4 sm:justify-end">
                                <div class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[0.9rem] font-semibold text-slate-800 shadow-sm">
                                    {{ number_format((int) data_get($item, 'quantity', 0)) }}
                                    x
                                    {{ data_get($item, 'unitAmountFormatted', '-') }}
                                </div>

                                <div class="min-w-[95px] text-right text-[0.95rem] font-bold text-slate-950">
                                    {{ data_get($item, 'lineAmountFormatted', '-') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            No line items found for this order.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-5 py-4">
                <p class="m-0 {{ $mutedTextClass }}">
                    This order contains {{ number_format($items->count()) }}
                    item{{ $items->count() === 1 ? '' : 's' }}.
                </p>
            </div>
        </section>

        <section class="{{ $cardClass }}">
            <div class="{{ $cardHeaderClass }}">
                <h2 class="{{ $sectionTitleClass }}">Order Summary</h2>
                <!-- <span class="text-2xl leading-none text-slate-400">⌄</span> -->
            </div>

            <div class="px-5 pb-5">
                <span class="pill {{ $badgeClass }} {{ $statusLabel }}">
                    {{ $statusLabel }}
                </span>

                <p class="mt-4 mb-6 {{ $mutedTextClass }}">
                    Review totals, payment status, and customer details for this order.
                </p>

                <div class="space-y-3 text-[0.95rem]">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Subtotal</span>

                        <div class="flex items-center gap-10 text-right">
                            <span class="hidden text-slate-500 sm:inline">
                                {{ number_format((int) data_get($order, 'quantity', $items->sum(fn ($item) => (int) data_get($item, 'quantity', 0)))) }}
                                item{{ ((int) data_get($order, 'quantity', 0)) === 1 ? '' : 's' }}
                            </span>

                            <span class="min-w-[90px] font-medium text-slate-700">
                                {{ data_get($order, 'subtotalAmountFormatted', '-') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Discount</span>

                        <div class="flex items-center gap-10 text-right">
                            <span class="hidden text-slate-500 sm:inline">
                                {{ data_get($order, 'discount.label', data_get($order, 'discount.code', 'None')) }}
                            </span>

                            <span class="min-w-[90px] font-medium text-slate-700">
                                {{ data_get($order, 'discountAmountFormatted', '-') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Shipping</span>

                        <div class="flex items-center gap-10 text-right">
                            <span class="hidden text-slate-500 sm:inline">
                                {{ data_get($order, 'shipping.address', '-') }}
                            </span>

                            <span class="min-w-[90px] font-medium text-slate-700">
                                -
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <span class="font-bold text-slate-950">Total</span>

                        <span class="min-w-[90px] text-right font-bold text-slate-950">
                            {{ data_get($order, 'totalAmountFormatted', '-') }}
                        </span>
                    </div>
                </div>

                <div class="my-6 border-t border-slate-200"></div>

                <div class="space-y-3 text-[0.95rem]">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Paid by customer</span>
                        <span class="font-medium text-slate-700">
                            {{ data_get($order, 'paidAtFormatted', '-') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Customer</span>
                        <span class="text-right font-medium text-slate-700">
                            {{ data_get($order, 'customerName', '-') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-600">Email</span>
                        <span class="text-right font-medium text-slate-700">
                            {{ data_get($order, 'customerEmail', '-') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-5 py-4">
                <p class="m-0 {{ $mutedTextClass }}">
                    Order created {{ data_get($order, 'createdAtFormatted', '-') }}.
                </p>
            </div>
        </section>

        <section class="{{ $cardClass }}">
            <div class="{{ $cardHeaderClass }}">
                <h2 class="{{ $sectionTitleClass }}">Payment History</h2>
                <!-- <span class="text-2xl leading-none text-slate-400">⌄</span> -->
            </div>

            <div class="px-5 pb-5">
                <p class="mb-6 mt-0 {{ $mutedTextClass }}">
                    Payment records associated with this order.
                </p>

                <div class="space-y-4">
                    @forelse ($payments as $payment)
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="{{ $badgeClass }} {{ data_get($payment, 'statusClass', 'bg-slate-50 text-slate-700 border-slate-200') }}">
                                            {{ data_get($payment, 'statusLabel', '-') }}
                                        </span>

                                        <span class="text-[0.8rem] font-mono text-slate-400">
                                            {{ data_get($payment, 'uid', '-') }}
                                        </span>
                                    </div>

                                    <p class="mt-3 mb-0 text-[0.9rem] text-slate-500">
                                        Intent:
                                        <span class="font-mono text-slate-700">
                                            {{ data_get($payment, 'stripePaymentIntentId', '-') }}
                                        </span>
                                    </p>

                                    @if (data_get($payment, 'failureMessage'))
                                        <p class="mt-2 mb-0 text-[0.85rem] text-rose-700">
                                            {{ data_get($payment, 'failureMessage') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="m-0 text-[1rem] font-bold text-slate-950">
                                        {{ data_get($payment, 'amountFormatted', '-') }}
                                    </p>

                                    <!-- check if refundedAtFormatted exists -->
                                     @if (data_get($payment, 'paidAtFormatted') && data_get($payment, 'paidAtFormatted') !== '-')
                                        <p class="mt-2 mb-0 text-[0.85rem] text-slate-500">
                                            Paid: {{ data_get($payment, 'paidAtFormatted', '-') }}
                                        </p>
                                    @endif

                                    <!-- @if (data_get($payment, 'refundedAtFormatted') && data_get($payment, 'refundedAtFormatted') !== '-')
                                        <p class="mt-1 mb-0 text-[0.85rem] text-slate-500">
                                            Refunded: {{ data_get($payment, 'refundedAtFormatted', '-') }}
                                        </p>
                                    @endif -->
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            No payments found for this order.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <div class="pb-6">
            <a
                href="{{ url('/admin/orders') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 no-underline transition hover:border-slate-300 hover:bg-slate-50"
            >
                Back to orders
            </a>
        </div>
    </div>
@endsection