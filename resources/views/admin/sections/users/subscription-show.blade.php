@extends('admin.layout')

@php
    $activeSection = 'users';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Subscription Details</h2>
        <p class="mt-[0.35rem] text-admin-muted">Review plan status, renewal dates, and the linked user account.</p>
    </header>

    <section class="border border-admin-stroke bg-white p-4">
        <div class="flex items-center justify-between gap-3 flex-wrap border-b border-[#e6edf3] pb-[0.8rem]">
            <div>
                <p class="m-0 text-slate-500 text-[0.74rem] tracking-[0.04em] uppercase font-bold">Subscription UID</p>
                <h3 id="subscription-uid" class="mt-1 text-[#1f2d39] text-base font-mono">{{ $subscriptionId }}</h3>
            </div>
            <a href="{{ url('/admin/users') }}" class="border border-[#d9e4ec] rounded-[0.6rem] py-[0.45rem] px-[0.7rem] text-slate-700 no-underline text-[0.82rem] font-semibold bg-white hover:border-slate-400">Back to users</a>
        </div>

        <p id="subscription-detail-status" class="orders-detail-status">Loading subscription details...</p>

        <div class="grid grid-cols-2 gap-[0.8rem] mt-[0.8rem]">
            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Subscription</h4>
                <dl id="subscription-summary" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">User</h4>
                <dl id="subscription-user" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Plan</h4>
                <dl id="subscription-plan" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>
        </div>
    </section>

    <template id="detail-row-template">
        <dt class="text-slate-500 text-[0.78rem] font-bold">{label}</dt>
        <dd class="m-0 text-[#243443] text-[0.84rem]">{value}</dd>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const subscriptionUid = @json($subscriptionId);

            const els = {
                status: document.getElementById('subscription-detail-status'),
                uid: document.getElementById('subscription-uid'),
                summary: document.getElementById('subscription-summary'),
                user: document.getElementById('subscription-user'),
                plan: document.getElementById('subscription-plan'),
            };

            let TemplateEngine;
            let dateTime;

            const initializeHelpers = () => {
                const helpers = window.ClasserHelpers || {};

                TemplateEngine = helpers.TemplateEngine || window.TemplateEngine;
                dateTime = helpers.dateTime;

                return (
                    !!TemplateEngine &&
                    typeof TemplateEngine.render === 'function' &&
                    typeof dateTime === 'function'
                );
            };

            const renderDetailRows = (root, rows) => {
                root.innerHTML = rows
                    .map((row) => TemplateEngine.render('detail-row-template', row))
                    .join('');
            };

            const toYesNo = (value) => {
                return value ? 'Yes' : 'No';
            };

            const toStatusText = (value) => {
                const normalized = String(value || '-').trim();
                if (!normalized || normalized === '-') {
                    return '-';
                }

                return normalized.charAt(0).toUpperCase() + normalized.slice(1);
            };

            const toFriendlyBytes = (value) => {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }

                const bytes = Number(value);
                if (!Number.isFinite(bytes) || bytes < 0) {
                    return String(value);
                }

                if (bytes === 0) {
                    return '0 B';
                }

                const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                const sized = bytes / (1024 ** unitIndex);
                const digits = sized >= 100 ? 0 : sized >= 10 ? 1 : 2;

                return `${sized.toFixed(digits)} ${units[unitIndex]}`;
            };

            const setStatus = (message, className = '') => {
                els.status.textContent = message;
                els.status.className = `orders-detail-status ${className}`.trim();
            };

            const renderPayload = (payload) => {
                const data = payload?.data || {};
                const user = payload?.user || null;
                const plan = data?.plan || {};

                els.uid.textContent = data.uid || subscriptionUid;
                setStatus('');

                renderDetailRows(els.summary, [
                    { label: 'Status', value: toStatusText(data.status) },
                    { label: 'Auto Renew', value: toYesNo(!!data.auto_renew) },
                    { label: 'Expiration Date', value: dateTime(data.expiration_date) },
                    { label: 'Auto Renew Date', value: dateTime(data.auto_renew_date) },
                    { label: 'Cancellation Date', value: dateTime(data.cancellation_date) },
                    { label: 'Cancellation Reason', value: data.cancellation_reason || '-' },
                ]);

                renderDetailRows(els.user, [
                    { label: 'User UID', value: user?.uid || data.user_id || '-' },
                    { label: 'Name', value: user?.name || '-' },
                    { label: 'Email', value: user?.email || '-' },
                    { label: 'Account Status', value: toStatusText(user?.account_status || '-') },
                ]);

                renderDetailRows(els.plan, [
                    { label: 'Plan UID', value: plan.uid || data.plan_id || '-' },
                    { label: 'Plan Code', value: plan.code || '-' },
                    { label: 'Quota', value: toFriendlyBytes(plan.quota) },
                    { label: 'Transaction ID', value: data.transaction_id || '-' },
                    { label: 'Notes', value: data.notes || '-' },
                    { label: 'Updated By', value: data.updated_by || '-' },
                ]);
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

                setStatus('Loading subscription details...');

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/users/subscription/${encodeURIComponent(subscriptionUid)}`, {
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load subscription details.');
                    }

                    const payload = await response.json();
                    renderPayload(payload);
                } catch (error) {
                    setStatus(error.message || 'Unable to load subscription details.', 'is-error');
                }
            };

            window.addEventListener('load', load, {
                once: true,
            });
        })();
    </script>
@endsection
