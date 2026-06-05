@extends('auth.admin.layout')

@php
    $activeSection = 'orders';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Orders</h2>
        <p>Monitor checkout orders with a single top-level status filter and quick search.</p>
    </header>

    <section class="admin-card orders-card">
        <div class="orders-toolbar">
            <div class="orders-toolbar-left">
                <label class="orders-search-wrap" for="orders-search">
                    <span class="orders-search-icon">⌕</span>
                    <input id="orders-search" type="search" placeholder="Search order, customer, or product">
                </label>

                <label class="orders-filter-wrap" for="orders-status-filter">
                    <span>Status</span>
                    <select id="orders-status-filter">
                        <option value="all">All</option>
                    </select>
                </label>
            </div>

            <p id="orders-count" class="orders-count">Loading...</p>
        </div>

        <div class="orders-table-wrap">
            <table class="orders-table" id="orders-table">
                <thead>
                    <tr>
                        <th>Order UID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="6" class="orders-empty">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="orders-pagination" class="orders-pagination" aria-label="Orders pagination"></div>
    </section>

    <template id="orders-row-template">
        <tr>
            <td><a class="orders-link" href="{orderUrl}"><span class="orders-code">{orderUid}</span></a></td>
            <td>{customer}</td>
            <td>{productName}</td>
            <td>{amount}</td>
            <td><span class="orders-status {statusClass}">{status}</span></td>
            <td>{createdAt}</td>
        </tr>
    </template>

    <template id="orders-empty-row-template">
        <tr>
            <td colspan="6" class="orders-empty {className}">{message}</td>
        </tr>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const tbody = document.getElementById('orders-table-body');
            const statusFilter = document.getElementById('orders-status-filter');
            const searchInput = document.getElementById('orders-search');
            const countLabel = document.getElementById('orders-count');
            const paginationRoot = document.getElementById('orders-pagination');

            const query = new URLSearchParams(window.location.search);
            const state = {
                q: query.get('q') || '',
                status: (query.get('status') || 'all').toLowerCase(),
                page: Number.parseInt(query.get('page') || '1', 10),
                limit: Number.parseInt(query.get('limit') || '20', 10),
            };

            if (!Number.isFinite(state.page) || state.page < 1) state.page = 1;
            if (!Number.isFinite(state.limit) || state.limit < 1) state.limit = 20;

            let pagination = null;
            let searchDebounce = null;
            let TemplateEngine;
            let money;
            let dateTime;
            let statusBadgeClass;

            searchInput.value = state.q;

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

            const renderEmptyRow = (message, className = '') => {
                if (!TemplateEngine || typeof TemplateEngine.render !== 'function') {
                    tbody.innerHTML = `<tr><td colspan="6" class="orders-empty ${className}">${message}</td></tr>`;
                    return;
                }

                tbody.innerHTML = TemplateEngine.render('orders-empty-row-template', {
                    className,
                    message,
                });
            };

            const updateUrl = () => {
                const params = new URLSearchParams();
                if (state.q) params.set('q', state.q);
                if (state.status && state.status !== 'all') params.set('status', state.status);
                if (state.page > 1) params.set('page', String(state.page));
                if (state.limit !== 20) params.set('limit', String(state.limit));
                const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
                window.history.replaceState({}, '', nextUrl);
            };

            const updateStatusOptions = (statuses) => {
                const uniqueStatuses = [...new Set((statuses || []).filter(Boolean).map((status) => String(status).toLowerCase()))].sort();

                const currentValue = statusFilter.value;
                statusFilter.innerHTML = '<option value="all">All</option>';

                uniqueStatuses.forEach((status) => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusFilter.appendChild(option);
                });

                if ([...statusFilter.options].some((option) => option.value === currentValue)) {
                    statusFilter.value = currentValue;
                } else {
                    statusFilter.value = 'all';
                }
            };

            const createPageButton = (label, page, disabled = false, active = false) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `orders-page-btn${active ? ' is-active' : ''}`;
                button.textContent = label;
                button.disabled = disabled;
                if (!disabled && !active) {
                    button.addEventListener('click', () => {
                        state.page = page;
                        loadOrders();
                    });
                }
                return button;
            };

            const renderPagination = () => {
                if (!pagination || !paginationRoot) {
                    return;
                }

                const current = Number(pagination.current_page || 1);
                const last = Number(pagination.last_page || 1);
                paginationRoot.innerHTML = '';

                if (last <= 1) {
                    return;
                }

                const nav = document.createElement('nav');
                nav.className = 'orders-pagination-nav';

                nav.appendChild(createPageButton('Previous', current - 1, current <= 1));

                const windowSize = 5;
                let start = Math.max(1, current - Math.floor(windowSize / 2));
                let end = Math.min(last, start + windowSize - 1);
                start = Math.max(1, end - windowSize + 1);

                if (start > 1) {
                    nav.appendChild(createPageButton('1', 1, false, current === 1));
                    if (start > 2) {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'orders-page-ellipsis';
                        ellipsis.textContent = '...';
                        nav.appendChild(ellipsis);
                    }
                }

                for (let page = start; page <= end; page += 1) {
                    nav.appendChild(createPageButton(String(page), page, false, page === current));
                }

                if (end < last) {
                    if (end < last - 1) {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'orders-page-ellipsis';
                        ellipsis.textContent = '...';
                        nav.appendChild(ellipsis);
                    }
                    nav.appendChild(createPageButton(String(last), last, false, current === last));
                }

                nav.appendChild(createPageButton('Next', current + 1, current >= last));
                paginationRoot.appendChild(nav);
            };

            const renderRows = (orders) => {
                const statusText = state.status === 'all' ? 'all statuses' : state.status;
                const total = Number(pagination?.total || 0);
                const from = Number(pagination?.from || 0);
                const to = Number(pagination?.to || 0);

                countLabel.textContent = total
                    ? `${from}-${to} of ${total} (${statusText})`
                    : `0 results (${statusText})`;

                if (!orders.length) {
                    renderEmptyRow('No orders match this filter.');
                    renderPagination();
                    return;
                }

                const rows = orders.map((order) => {
                    const uid = String(order.uid || '-');

                    return TemplateEngine.render('orders-row-template', {
                        orderUrl: `${window.pageUrl}/auth/admin/orders/${encodeURIComponent(uid)}`,
                        orderUid: uid,
                        customer: order.customer_email || '-',
                        productName: order.product?.name || '-',
                        amount: money(order.currency, order.amount),
                        statusClass: statusBadgeClass(order.status),
                        status: order.status || '-',
                        createdAt: dateTime(order.created_at),
                    });
                });

                tbody.innerHTML = rows.join('');
                renderPagination();
            };

            const loadOrders = () => {
                if (!initializeHelpers()) {
                    renderEmptyRow('Global frontend helpers are not available.', 'is-error');
                    countLabel.textContent = 'Unavailable';
                    return;
                }

                updateUrl();

                renderEmptyRow('Loading...');
                countLabel.textContent = 'Loading...';

                const params = new URLSearchParams();
                params.set('page', String(state.page));
                params.set('limit', String(state.limit));
                if (state.status && state.status !== 'all') {
                    params.set('status', state.status);
                }
                if (state.q) {
                    params.set('q', state.q);
                }

                fetch(`${window.pageUrl}/api/admin/orders?${params.toString()}`, {
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Unable to load orders.');
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        pagination = payload.pagination || null;

                        const serverStatus = String(payload?.filters?.status || state.status || 'all').toLowerCase();
                        state.status = serverStatus || 'all';
                        statusFilter.value = state.status;

                        updateStatusOptions(payload.status_options || []);
                        statusFilter.value = state.status;

                        renderRows(payload.data || []);
                    })
                    .catch((error) => {
                        renderEmptyRow(error.message || 'Unable to load orders.', 'is-error');
                        countLabel.textContent = 'Unavailable';
                        if (paginationRoot) paginationRoot.innerHTML = '';
                    });
            };

            if (!token) {
                renderEmptyRow('Missing admin token. Please login again.', 'is-error');
                countLabel.textContent = 'Unavailable';
                return;
            }

            searchInput.addEventListener('input', () => {
                const next = String(searchInput.value || '').trim();
                state.q = next;
                state.page = 1;

                if (searchDebounce) {
                    window.clearTimeout(searchDebounce);
                }

                searchDebounce = window.setTimeout(loadOrders, 250);
            });

            statusFilter.addEventListener('change', () => {
                state.status = String(statusFilter.value || 'all').toLowerCase();
                state.page = 1;
                loadOrders();
            });

            if (document.readyState === 'complete') {
                loadOrders();
            } else {
                window.addEventListener('load', loadOrders, {
                    once: true,
                });
            }
        })();
    </script>
@endsection
