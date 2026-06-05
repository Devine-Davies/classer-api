@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Discount Codes</h2>
        <p>Manage percentage-based discounts and review usage counts before disabling campaigns.</p>
    </header>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Discount list</h3>
                <p class="mt-1 text-sm text-slate-500">Only generic eligibility messages are exposed to checkout users.</p>
            </div>
            <a href="{{ url('/auth/admin/discount-codes/add') }}" class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white">Add discount code</a>
        </div>

        <div id="discount-codes-empty" class="px-5 py-4 text-sm text-slate-500">Loading discount codes...</div>
        <div id="discount-codes-list" class="divide-y divide-slate-100"></div>
    </section>

    <template id="discount-code-row-template">
        <article class="px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-semibold text-slate-900">{code}</h4>
                    <span class="rounded-full px-2 py-0.5 text-xs {activeClass}">{activeLabel}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs bg-sky-100 text-sky-700">{percentage}%</span>
                    <span class="rounded-full px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700">Used {usageCount}{usageLimit}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600">{restriction}</p>
                <p class="mt-1 text-xs text-slate-500">{availability}</p>
            </div>
            <a href="{openUrl}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">Open</a>
        </article>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const list = document.getElementById('discount-codes-list');
            const empty = document.getElementById('discount-codes-empty');
            let TemplateEngine;

            const initializeHelpers = () => {
                const helpers = window.ClasserHelpers || {};
                TemplateEngine = helpers.TemplateEngine || window.TemplateEngine;
                return !!TemplateEngine && typeof TemplateEngine.render === 'function';
            };

            const toRestriction = (code) => {
                const product = code.product_id ? 'Product restricted' : 'Any product';
                const user = code.assigned_user_id || code.assigned_email ? 'Assigned' : 'Any customer';
                return `${product} · ${user}`;
            };

            const toAvailability = (code) => {
                const starts = code.starts_at ? new Date(code.starts_at).toLocaleDateString() : 'Now';
                const expires = code.expires_at ? new Date(code.expires_at).toLocaleDateString() : 'No expiry';
                return `Starts: ${starts} · Expires: ${expires}`;
            };

            const renderList = (codes) => {
                if (!codes.length) {
                    empty.textContent = 'No discount codes found.';
                    list.innerHTML = '';
                    return;
                }

                empty.textContent = '';
                list.innerHTML = codes
                    .map((code) => TemplateEngine.render('discount-code-row-template', {
                        code: code.code || '-',
                        activeClass: code.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600',
                        activeLabel: code.is_active ? 'Active' : 'Inactive',
                        percentage: Number(code.discount_percentage || 0),
                        usageCount: Number(code.usage_count || 0),
                        usageLimit: code.usage_limit ? ` / ${code.usage_limit}` : '',
                        restriction: toRestriction(code),
                        availability: toAvailability(code),
                        openUrl: `${window.pageUrl}/auth/admin/discount-codes/${encodeURIComponent(String(code.uid || ''))}`,
                    }))
                    .join('');
            };

            const fetchCodes = async () => {
                if (!initializeHelpers()) {
                    empty.textContent = 'Global frontend helpers are not available.';
                    return;
                }

                if (!token) {
                    empty.textContent = 'Missing admin token. Please login again.';
                    return;
                }

                const response = await fetch(`${window.pageUrl}/api/admin/discount-codes`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load discount codes.');
                }

                const payload = await response.json();
                renderList(payload.data || []);
            };

            if (document.readyState === 'complete') {
                fetchCodes().catch((error) => {
                    empty.textContent = error.message;
                });
            } else {
                window.addEventListener('load', () => {
                    fetchCodes().catch((error) => {
                        empty.textContent = error.message;
                    });
                }, { once: true });
            }
        })();
    </script>
@endsection
