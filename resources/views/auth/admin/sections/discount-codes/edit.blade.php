@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Edit Discount Code</h2>
        <p>Update campaign availability and administrative metadata.</p>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h3 class="text-lg font-semibold text-slate-900" id="discount-code-title">Loading discount code...</h3>
                <p id="discount-code-status" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <a href="{{ url('/auth/admin/discount-codes') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">Back to discount codes</a>
        </div>

        <form id="discount-code-form" class="mt-5 space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="code">Code</label>
                    <input id="code" name="code" type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase focus:border-[var(--admin-primary)] focus:outline-none">
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
                <input id="is_active" name="is_active" type="checkbox">
                Discount code is active
            </label>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 ml-4">
                <input id="one_use_per_customer" name="one_use_per_customer" type="checkbox">
                One use per customer
            </label>

            <p id="discount-code-form-message" class="text-sm"></p>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white">Update discount code</button>
                <button type="button" id="discount-code-disable" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700">Disable</button>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const discountCodeUid = @json($discountCodeUid);
            const form = document.getElementById('discount-code-form');
            const message = document.getElementById('discount-code-form-message');
            const title = document.getElementById('discount-code-title');
            const status = document.getElementById('discount-code-status');
            const disableButton = document.getElementById('discount-code-disable');
            const productSelect = document.getElementById('product_id');

            let currentData = null;

            const setMessage = (text, isError = false) => {
                message.textContent = text;
                message.className = isError ? 'text-sm text-red-600' : 'text-sm text-emerald-600';
            };

            const toDateTimeLocal = (value) => {
                if (!value) {
                    return '';
                }

                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
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

            const applyCode = (code) => {
                currentData = code;
                title.textContent = code.code || 'Edit discount code';
                status.textContent = `Usage: ${Number(code.usage_count || 0)}${code.usage_limit ? ` / ${code.usage_limit}` : ''}`;

                form.code.value = code.code || '';
                form.discount_percentage.value = code.discount_percentage ?? '';
                form.usage_limit.value = code.usage_limit ?? '';
                form.min_order_amount.value = code.min_order_amount ?? '';
                form.assigned_email.value = code.assigned_email || '';
                form.product_id.value = code.product_id || '';
                form.starts_at.value = toDateTimeLocal(code.starts_at);
                form.expires_at.value = toDateTimeLocal(code.expires_at);
                form.internal_note.value = code.internal_note || '';
                form.is_active.checked = Boolean(code.is_active);
                form.one_use_per_customer.checked = Boolean(code.one_use_per_customer);

                const isRedeemed = Number(code.usage_count || 0) > 0;
                const lockFields = ['code', 'discount_percentage', 'product_id'];
                lockFields.forEach((name) => {
                    if (form[name]) {
                        form[name].disabled = isRedeemed;
                    }
                });
            };

            const fetchCode = async () => {
                const response = await fetch(`${window.pageUrl}/api/admin/discount-codes/${discountCodeUid}`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load discount code.');
                }

                const result = await response.json();
                applyCode(result.data);
            };

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

                if (Number(currentData?.usage_count || 0) > 0) {
                    delete payload.code;
                    delete payload.discount_percentage;
                    delete payload.product_id;
                }

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/discount-codes/${discountCodeUid}`, {
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
                        const errorText = result?.message || Object.values(result?.errors || {}).flat().join(' ') || 'Unable to update discount code.';
                        throw new Error(errorText);
                    }

                    setMessage(result.message || 'Discount code updated.');
                    applyCode(result.data);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            disableButton.addEventListener('click', async () => {
                setMessage('');

                if (!window.confirm('Disable this discount code? Checkout customers will no longer be able to apply it.')) {
                    return;
                }

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/discount-codes/${discountCodeUid}/disable`, {
                        method: 'PATCH',
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    const result = await response.json();
                    if (!response.ok || result.status === false) {
                        throw new Error(result?.message || 'Unable to disable discount code.');
                    }

                    setMessage(result.message || 'Discount code disabled.');
                    applyCode(result.data);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            Promise.all([loadProducts(), fetchCode()]).catch((error) => {
                setMessage(error.message, true);
                title.textContent = 'Unable to load discount code';
            });
        })();
    </script>
@endsection
