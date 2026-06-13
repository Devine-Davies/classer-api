@extends('auth.admin.layout')

@php
    $activeSection = 'orders';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Order Details</h2>
        <p class="mt-[0.35rem] text-admin-muted">Review order, line items, payment history, and customer delivery info.</p>
    </header>

    @php
        $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    @endphp

    <section class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)] p-4">
        <div class="flex items-center justify-between gap-3 flex-wrap border-b border-[#e6edf3] pb-[0.8rem]">
            <div>
                <p class="m-0 text-slate-500 text-[0.74rem] tracking-[0.04em] uppercase font-bold">Order UID</p>
                <h3 id="order-uid" class="mt-1 text-[#1f2d39] text-base font-mono">{{ $orderUid }}</h3>
            </div>
            <a href="{{ url('/auth/admin/orders') }}" class="border border-[#d9e4ec] rounded-[0.6rem] py-[0.45rem] px-[0.7rem] text-slate-700 no-underline text-[0.82rem] font-semibold bg-white hover:border-slate-400">Back to orders</a>
        </div>

        <p id="order-detail-status" class="orders-detail-status">Loading order details...</p>

        <div class="grid grid-cols-2 gap-[0.8rem] mt-[0.8rem]">
            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Summary</h4>
                <dl id="order-summary" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Customer</h4>
                <dl id="order-customer" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Catalog Items</h4>
                <div class="mt-[0.6rem] overflow-x-auto">
                    <table class="w-full border-collapse min-w-[680px]">
                        <thead>
                            <tr class="bg-[#eef3f7]">
                                <th class="{{ $thClass }}">Items</th>
                                <th class="{{ $thClass }}">Type</th>
                                <th class="{{ $thClass }}">Unit</th>
                                <th class="{{ $thClass }}">Qty</th>
                                <th class="{{ $thClass }}">Total</th>
                            </tr>
                        </thead>
                        <tbody id="order-items-body">
                            <tr>
                                <td colspan="5" class="orders-empty">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Payments</h4>
                <div class="mt-[0.6rem] overflow-x-auto">
                    <table class="w-full border-collapse min-w-[680px]">
                        <thead>
                            <tr class="bg-[#eef3f7]">
                                <th class="{{ $thClass }}">Payment UID</th>
                                <th class="{{ $thClass }}">Status</th>
                                <th class="{{ $thClass }}">Amount</th>
                                <th class="{{ $thClass }}">Intent</th>
                                <th class="{{ $thClass }}">Paid At</th>
                            </tr>
                        </thead>
                        <tbody id="order-payments-body">
                            <tr>
                                <td colspan="5" class="orders-empty">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>

    <template id="detail-row-template">
        <dt class="text-slate-500 text-[0.78rem] font-bold">{label}</dt>
        <dd class="m-0 text-[#243443] text-[0.84rem]">{value}</dd>
    </template>

    <template id="item-row-template">
        <tr>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{nameSnapshot}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{type}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{unitAmount}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{quantity}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{lineAmount}</td>
        </tr>
    </template>

    <template id="payment-row-template">
        <tr>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-code">{uid}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-status {statusClass}">{status}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{amount}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{paymentIntent}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{paidAt}</td>
        </tr>
    </template>

    <template id="empty-row-template">
        <tr>
            <td colspan="{columns}" class="orders-empty {className}">{message}</td>
        </tr>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const orderUid = @json($orderUid);

            const els = {
                status: document.getElementById('order-detail-status'),
                orderUid: document.getElementById('order-uid'),
                summary: document.getElementById('order-summary'),
                customer: document.getElementById('order-customer'),
                itemsBody: document.getElementById('order-items-body'),
                paymentsBody: document.getElementById('order-payments-body'),
            };

            let TemplateEngine;
            let money;
            let dateTime;
            let statusBadgeClass;

            const initializeHelpers = () => {
                const helpers = window.ClasserHelpers || {};

                TemplateEngine = helpers.TemplateEngine || window.TemplateEngine;
                money = helpers.money;
                dateTime = helpers.dateTime;
                statusBadgeClass = helpers.statusBadgeClass;

                return (
                    !!TemplateEngine &&
                    typeof TemplateEngine.render === 'function' &&
                    typeof money === 'function' &&
                    typeof dateTime === 'function' &&
                    typeof statusBadgeClass === 'function'
                );
            };

            const renderDetailRows = (root, rows) => {
                root.innerHTML = rows
                    .map((row) => TemplateEngine.render('detail-row-template', row))
                    .join('');
            };

            const renderSummary = (order) => {
                renderDetailRows(els.summary, [{
                        label: 'Status',
                        value: order.status,
                    },
                    {
                        label: 'Amount',
                        value: money(order.currency, order.amount),
                    },
                    {
                        label: 'Total Qty',
                        value: order.quantity,
                    },
                    {
                        label: 'Created',
                        value: dateTime(order.created_at),
                    },
                    {
                        label: 'Paid At',
                        value: dateTime(order.paid_at),
                    },
                ]);
            };

            const renderCustomer = (order) => {
                const shipping = order.shipping || {};

                const shippingLines = [
                    shipping.line_1,
                    shipping.line_2,
                    shipping.city,
                    shipping.state,
                    shipping.postal_code,
                    shipping.country,
                ].filter(Boolean).join(', ');

                renderDetailRows(els.customer, [{
                        label: 'Name',
                        value: order.customer_name,
                    },
                    {
                        label: 'Email',
                        value: order.customer_email,
                    },
                    {
                        label: 'Shipping',
                        value: shippingLines,
                    },
                ]);
            };

            const renderItems = (order) => {
                const items = Array.isArray(order.items) ? order.items : [];

                if (!items.length) {
                    els.itemsBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 5,
                        className: '',
                        message: 'No line items found.',
                    });

                    return;
                }

                els.itemsBody.innerHTML = items
                    .map((item) => {
                        return TemplateEngine.render('item-row-template', {
                            nameSnapshot: item.name_snapshot || '-',
                            type: item.type || '-',
                            quantity: item.quantity,
                            unitAmount: money(item.currency, item.unit_amount),
                            lineAmount: money(item.currency, item.line_amount),
                        });
                    })
                    .join('');
            };

            const renderPayments = (payments) => {
                const rows = Array.isArray(payments) ? payments : [];

                if (!rows.length) {
                    els.paymentsBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 5,
                        className: '',
                        message: 'No payments found.',
                    });

                    return;
                }

                els.paymentsBody.innerHTML = rows
                    .map((payment) => {
                        return TemplateEngine.render('payment-row-template', {
                            uid: payment.uid || '-',
                            status: payment.status || '-',
                            statusClass: statusBadgeClass(payment.status),
                            amount: money(payment.currency, payment.amount),
                            paymentIntent: payment.stripe_payment_intent_id || '-',
                            paidAt: dateTime(payment.paid_at),
                        });
                    })
                    .join('');
            };

            const setStatus = (message, className = '') => {
                els.status.textContent = message;
                els.status.className = `orders-detail-status ${className}`.trim();
            };

            const load = async () => {
                if (!initializeHelpers()) {
                    setStatus('Global frontend helpers are not available.', 'is-error');
                    return;
                }

                if (!token) {
                    setStatus('Missing admin token. Please login again.', 'is-error');
                    return;
                }

                setStatus('Loading order details...');

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/orders/${orderUid}`, {
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load order details.');
                    }

                    const payload = await response.json();
                    const order = payload?.data || {};

                    els.orderUid.textContent = order.uid || orderUid;
                    setStatus(``);

                    renderSummary(order);
                    renderCustomer(order);
                    renderItems(order);
                    renderPayments(payload?.payments || []);
                } catch (error) {
                    setStatus(error.message || 'Unable to load order details.', 'is-error');

                    els.itemsBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 5,
                        className: 'is-error',
                        message: 'Unable to load line items.',
                    });

                    els.paymentsBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 5,
                        className: 'is-error',
                        message: 'Unable to load payments.',
                    });
                }
            };

            window.addEventListener('load', load, {
                once: true,
            });
        })();
    </script>
@endsection
