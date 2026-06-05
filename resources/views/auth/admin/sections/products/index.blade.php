@extends('auth.admin.layout')

@php
    $activeSection = 'products';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Products</h2>
        <p>List all products, including soft-deleted products kept for audit/history.</p>
    </header>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Product list</h3>
                <p class="mt-1 text-sm text-slate-500">Deleted products remain visible here with a deleted indicator.</p>
            </div>
            <a href="{{ url('/auth/admin/products/add') }}" class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white">Add product</a>
        </div>

        <div id="products-empty" class="px-5 py-4 text-sm text-slate-500">Loading products...</div>
        <div id="products-list" class="divide-y divide-slate-100"></div>
    </section>

    <template id="product-row-template">
        <article class="px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-semibold text-slate-900">{name}</h4>
                    <span class="rounded-full px-2 py-0.5 text-xs {activeClass}">{activeLabel}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700">{purchaseType}</span>
                    <span class="{deletedClass}">{deletedLabel}</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 font-mono">{slug}</p>
                <p class="mt-2 text-sm text-slate-600">{description}</p>
                <p class="mt-3 text-sm font-medium text-slate-900">{amount}</p>
            </div>
            <a href="{openUrl}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">Open</a>
        </article>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const list = document.getElementById('products-list');
            const empty = document.getElementById('products-empty');
            let products = [];
            let TemplateEngine;
            let money;

            const initializeHelpers = () => {
                const helpers = window.ClasserHelpers || {};

                TemplateEngine = helpers.TemplateEngine || window.TemplateEngine;
                money = helpers.money;

                return (
                    !!TemplateEngine &&
                    typeof TemplateEngine.render === 'function' &&
                    typeof money === 'function'
                );
            };

            const renderList = () => {
                if (!products.length) {
                    empty.textContent = 'No products found.';
                    list.innerHTML = '';
                    return;
                }

                empty.textContent = '';
                list.innerHTML = products
                    .map((product) => {
                        return TemplateEngine.render('product-row-template', {
                            name: product.name || '-',
                            activeClass: product.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600',
                            activeLabel: product.is_active ? 'Active' : 'Inactive',
                            purchaseType: String(product.purchase_type || 'one_time').replaceAll('_', ' '),
                            deletedClass: product.deleted_at ? 'rounded-full px-2 py-0.5 text-xs bg-rose-100 text-rose-700' : 'hidden',
                            deletedLabel: 'Deleted',
                            slug: product.slug || '-',
                            description: product.description || '',
                            amount: money(product.currency, product.price_amount),
                            openUrl: `${window.pageUrl}/auth/admin/products/${encodeURIComponent(String(product.uid || ''))}`,
                        });
                    })
                    .join('');
            };

            const fetchProducts = async () => {
                if (!initializeHelpers()) {
                    empty.textContent = 'Global frontend helpers are not available.';
                    return;
                }

                if (!token) {
                    empty.textContent = 'Missing admin token. Please login again.';
                    return;
                }

                const response = await fetch(`${window.pageUrl}/api/admin/products`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load products.');
                }

                const payload = await response.json();
                products = payload.data || [];
                renderList();
            };

            if (document.readyState === 'complete') {
                fetchProducts().catch((error) => {
                    empty.textContent = error.message;
                });
            } else {
                window.addEventListener('load', () => {
                    fetchProducts().catch((error) => {
                        empty.textContent = error.message;
                    });
                }, {
                    once: true,
                });
            }
        })();
    </script>
@endsection
