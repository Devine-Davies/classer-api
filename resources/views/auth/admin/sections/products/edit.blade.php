@extends('auth.admin.layout')

@php
    $activeSection = 'products';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Edit Product</h2>
        <p>Update product details or soft delete the product.</p>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h3 class="text-lg font-semibold text-slate-900" id="product-title">Loading product...</h3>
                <p id="product-status" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <a href="{{ url('/auth/admin/products') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">Back to products</a>
        </div>

        <form id="product-form" class="mt-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700" for="sku">SKU</label>
                <input id="sku" name="sku" type="text" maxlength="64" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono uppercase focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="slug">Slug</label>
                <input id="slug" name="slug" type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="name">Name</label>
                <input id="name" name="name" type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="short_description">Short description</label>
                <input id="short_description" name="short_description" type="text" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="long_description">Long description</label>
                <textarea id="long_description" name="long_description" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none"></textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="price_amount">Price amount (minor units)</label>
                    <input id="price_amount" name="price_amount" type="number" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="currency">Currency</label>
                    <input id="currency" name="currency" type="text" maxlength="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase focus:border-[var(--admin-primary)] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="promotion_percentage">Promotion / discount (%)</label>
                <input id="promotion_percentage" name="promotion_percentage" type="number" min="0" max="100" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="purchase_type">Purchase type</label>
                <select id="purchase_type" name="purchase_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                    <option value="one_time">One time</option>
                    <option value="monthly">Monthly</option>
                    <option value="annually">Annually</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="image_url">Image URL</label>
                <input id="image_url" name="image_url" type="url" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input id="is_active" name="is_active" type="checkbox">
                Product is active
            </label>

            <p id="product-form-message" class="text-sm"></p>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white">Update product</button>
                <button type="button" id="product-delete" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700">Soft delete</button>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const productUid = @json($productUid);
            const form = document.getElementById('product-form');
            const message = document.getElementById('product-form-message');
            const title = document.getElementById('product-title');
            const status = document.getElementById('product-status');
            const deleteButton = document.getElementById('product-delete');

            const setMessage = (text, isError = false) => {
                message.textContent = text;
                message.className = isError ? 'text-sm text-red-600' : 'text-sm text-emerald-600';
            };

            const applyProduct = (product) => {
                title.textContent = product.name || 'Edit product';
                status.textContent = product.deleted_at
                    ? `Deleted at ${new Date(product.deleted_at).toLocaleString()}`
                    : (product.is_active ? 'Active product' : 'Inactive product');
                form.sku.value = product.sku || '';
                form.slug.value = product.slug || '';
                form.name.value = product.name || '';
                form.short_description.value = product.short_description || '';
                form.long_description.value = product.long_description || product.description || '';
                form.price_amount.value = product.price_amount ?? '';
                form.promotion_percentage.value = product.promotion_percentage ?? 0;
                form.currency.value = String(product.currency || 'GBP').toUpperCase();
                form.purchase_type.value = String(product.purchase_type || 'one_time');
                form.image_url.value = product.image_url || '';
                form.is_active.checked = Boolean(product.is_active);
                deleteButton.disabled = Boolean(product.deleted_at);
                deleteButton.textContent = product.deleted_at ? 'Already deleted' : 'Soft delete';
            };

            const fetchProduct = async () => {
                const response = await fetch(`${window.pageUrl}/api/admin/products/${productUid}`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load product.');
                }

                const result = await response.json();
                applyProduct(result.data);
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                setMessage('');

                const payload = {
                    sku: form.sku.value,
                    slug: form.slug.value,
                    name: form.name.value,
                    short_description: form.short_description.value || null,
                    long_description: form.long_description.value || null,
                    price_amount: Number(form.price_amount.value),
                    promotion_percentage: Number(form.promotion_percentage.value || 0),
                    currency: form.currency.value,
                    purchase_type: form.purchase_type.value,
                    image_url: form.image_url.value || null,
                    is_active: form.is_active.checked,
                };

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/products/${productUid}`, {
                        method: 'PATCH',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();
                    if (!response.ok || result.status === false) {
                        const errorText = result?.message || Object.values(result?.errors || {}).flat().join(' ') || 'Unable to update product.';
                        throw new Error(errorText);
                    }

                    setMessage(result.message || 'Product updated.');
                    applyProduct(result.data);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            deleteButton.addEventListener('click', async () => {
                setMessage('');

                if (!window.confirm('Soft delete this product? It will remain visible in admin but be removed from active store queries.')) {
                    return;
                }

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/products/${productUid}`, {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    const result = await response.json();
                    if (!response.ok || result.status === false) {
                        throw new Error(result?.message || 'Unable to delete product.');
                    }

                    setMessage(result.message || 'Product deleted.');
                    applyProduct(result.data);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            fetchProduct().catch((error) => {
                setMessage(error.message, true);
                title.textContent = 'Unable to load product';
            });
        })();
    </script>
@endsection