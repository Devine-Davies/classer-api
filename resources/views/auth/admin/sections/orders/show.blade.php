@extends('auth.admin.layout')

@php
    $activeSection = 'orders';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Order Details</h2>
        <p>Review order, line items, payment history, and customer delivery info.</p>
    </header>

    <section class="admin-card orders-detail-card">
        <div class="orders-detail-head">
            <div>
                <p class="orders-detail-label">Order UID</p>
                <h3 id="order-uid" class="orders-detail-code">{{ $orderUid }}</h3>
            </div>
            <a href="{{ url('/auth/admin/orders') }}" class="orders-detail-back">Back to orders</a>
        </div>

        <p id="order-detail-status" class="orders-detail-status">Loading order details...</p>

        <div class="orders-detail-grid">
            <section class="orders-detail-panel">
                <h4>Summary</h4>
                <dl id="order-summary" class="orders-detail-list"></dl>
            </section>

            <section class="orders-detail-panel">
                <h4>Customer</h4>
                <dl id="order-customer" class="orders-detail-list"></dl>
            </section>

            <section class="orders-detail-panel orders-detail-panel-wide">
                <h4>Line Items</h4>
                <div class="orders-detail-table-wrap">
                    <table class="orders-table orders-detail-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Line Total</th>
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

            <section class="orders-detail-panel orders-detail-panel-wide">
                <h4>Payments</h4>
                <div class="orders-detail-table-wrap">
                    <table class="orders-table orders-detail-table">
                        <thead>
                            <tr>
                                <th>Payment UID</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Intent</th>
                                <th>Paid At</th>
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
        <dt>{label}</dt>
        <dd>{value}</dd>
    </template>

    <template id="item-row-template">
        <tr>
            <td>{productName}</td>
            <td>{purchaseType}</td>
            <td>{unitAmount}</td>
            <td>{quantity}</td>
            <td>{lineAmount}</td>
        </tr>
    </template>

    <template id="payment-row-template">
        <tr>
            <td><span class="orders-code">{uid}</span></td>
            <td><span class="orders-status {statusClass}">{status}</span></td>
            <td>{amount}</td>
            <td>{paymentIntent}</td>
            <td>{paidAt}</td>
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
                            productName: item.product_name || item.product?.name || '-',
                            purchaseType: item.purchase_type || '-',
                            unitAmount: money(item.currency, item.unit_amount),
                            quantity: item.quantity,
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
