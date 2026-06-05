@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Add Discount Code</h2>
        <p>Create a percentage-only discount campaign for checkout.</p>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900">New discount code</h3>
            <a href="{{ url('/auth/admin/discount-codes') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">Back to discount codes</a>
        </div>

        <form id="discount-code-form" class="mt-5 space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="code">Code</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input id="code" name="code" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 uppercase focus:border-[var(--admin-primary)] focus:outline-none">
                        <button type="button" id="generate-code" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Generate</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="discount_percentage">Discount percentage</label>
                    <input id="discount_percentage" name="discount_percentage" type="number" min="1" max="99" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="usage_limit">Usage limit</label>
                    <input id="usage_limit" name="usage_limit" type="number" min="1" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="min_order_amount">Minimum order amount (minor units)</label>
                    <input id="min_order_amount" name="min_order_amount" type="number" min="1" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="assigned_email">Assigned email (optional)</label>
                    <input id="assigned_email" name="assigned_email" type="email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="product_id">Product restriction (optional)</label>
                    <select id="product_id" name="product_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                        <option value="">Any product</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="starts_at">Starts at</label>
                    <input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="expires_at">Expires at</label>
                    <input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="internal_note">Internal note</label>
                <textarea id="internal_note" name="internal_note" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none"></textarea>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input id="is_active" name="is_active" type="checkbox" checked>
                Discount code is active
            </label>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 ml-4">
                <input id="one_use_per_customer" name="one_use_per_customer" type="checkbox">
                One use per customer
            </label>

            <p id="discount-code-form-message" class="text-sm"></p>

            <button type="submit" class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white">Create discount code</button>
        </form>
    </section>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const form = document.getElementById('discount-code-form');
            const message = document.getElementById('discount-code-form-message');
            const generateCodeButton = document.getElementById('generate-code');
            const productSelect = document.getElementById('product_id');

            const setMessage = (text, isError = false) => {
                message.textContent = text;
                message.className = isError ? 'text-sm text-red-600' : 'text-sm text-emerald-600';
            };

            const toIsoString = (value) => {
                if (!value) {
                    return null;
                }

                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return null;
                }

                return date.toISOString();
            };

            const generateRandomCode = () => {
                const random = Math.random().toString(36).slice(2, 8).toUpperCase();
                return `SAVE-${random}`;
            };

            const loadProducts = async () => {
                const response = await fetch(`${window.pageUrl}/api/admin/products`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load products for restriction dropdown.');
                }

                const result = await response.json();
                const products = result.data || [];

                productSelect.innerHTML = '<option value="">Any product</option>';
                products
                    .filter((product) => !product.deleted_at)
                    .forEach((product) => {
                        const option = document.createElement('option');
                        option.value = product.uid;
                        option.textContent = `${product.name} (${String(product.currency || '').toUpperCase()} ${product.price_amount})`;
                        productSelect.appendChild(option);
                    });
            };

            if (!form.code.value) {
                form.code.value = generateRandomCode();
            }

            generateCodeButton.addEventListener('click', () => {
                form.code.value = generateRandomCode();
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                setMessage('');

                const payload = {
                    code: String(form.code.value || '').trim().toUpperCase(),
                    discount_percentage: Number(form.discount_percentage.value),
                    usage_limit: form.usage_limit.value ? Number(form.usage_limit.value) : null,
                    min_order_amount: form.min_order_amount.value ? Number(form.min_order_amount.value) : null,
                    assigned_email: form.assigned_email.value || null,
                    product_id: form.product_id.value || null,
                    starts_at: toIsoString(form.starts_at.value),
                    expires_at: toIsoString(form.expires_at.value),
                    internal_note: form.internal_note.value || null,
                    is_active: form.is_active.checked,
                    one_use_per_customer: form.one_use_per_customer.checked,
                };

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/discount-codes`, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();
                    if (!response.ok || result.status === false) {
                        const errorText = result?.message || Object.values(result?.errors || {}).flat().join(' ') || 'Unable to create discount code.';
                        throw new Error(errorText);
                    }

                    window.location.assign(`${window.pageUrl}/auth/admin/discount-codes/${result.data.uid}`);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            loadProducts().catch((error) => {
                setMessage(error.message, true);
            });
        })();
    </script>
@endsection
