@php
    $discountCode = $discountCode ?? $entity ?? null;

    $isEdit = $isEdit ?? false;

    $formId = $formId ?? 'discount-code-form';

    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $submitLabel = $submitLabel ?? ($isEdit ? 'Update discount code' : 'Create discount code');

    $cancelUrl = $cancelUrl ?? url('/auth/admin/discount-codes');

    $disableUrl = $disableUrl ?? null;

    $showDisable = $showDisable ?? ($isEdit && $disableUrl);

    $catalogItems = $catalogItems ?? collect();

    $codeValue = old('code', $discountCode->code ?? '');
    $discountPercentageValue = old('discount_percentage', $discountCode->discount_percentage ?? '');
    $usageLimitValue = old('usage_limit', $discountCode->usage_limit ?? '');
    $minOrderAmountValue = old('min_order_amount', $discountCode->min_order_amount ?? '');
    $assignedEmailValue = old('assigned_email', $discountCode->assigned_email ?? '');
    $catalogItemIdValue = old('catalog_item_id', $discountCode->catalog_item_id ?? '');
    $internalNoteValue = old('internal_note', $discountCode->internal_note ?? '');

    $startsAtValue = old(
        'starts_at',
        isset($discountCode->starts_at) && $discountCode->starts_at
            ? $discountCode->starts_at->format('Y-m-d\TH:i')
            : ''
    );

    $expiresAtValue = old(
        'expires_at',
        isset($discountCode->expires_at) && $discountCode->expires_at
            ? $discountCode->expires_at->format('Y-m-d\TH:i')
            : ''
    );

    $isActiveValue = old('is_active', $discountCode->is_active ?? true);
    $oneUsePerCustomerValue = old('one_use_per_customer', $discountCode->one_use_per_customer ?? true);
@endphp

@if (session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">Please fix the following errors:</p>

        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    id="{{ $formId }}"
    class="space-y-5"
    action="{{ $action }}"
    method="POST"
>
    @csrf

    @if (! in_array($method, ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="code">
                Code <span class="text-rose-600">*</span>
            </label>

            <input
                id="code"
                name="code"
                type="text"
                maxlength="120"
                required
                value="{{ $codeValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm uppercase text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('code') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="WELCOME20"
            >

            @error('code')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="discount_percentage">
                Discount percentage <span class="text-rose-600">*</span>
            </label>

            <input
                id="discount_percentage"
                name="discount_percentage"
                type="number"
                min="1"
                max="99"
                step="1"
                required
                value="{{ $discountPercentageValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('discount_percentage') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="20"
            >

            @error('discount_percentage')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="usage_limit">
                Usage limit
            </label>

            <input
                id="usage_limit"
                name="usage_limit"
                type="number"
                min="1"
                step="1"
                value="{{ $usageLimitValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('usage_limit') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="100"
            >

            <p class="mt-1 text-xs text-slate-500">
                Leave empty for no usage limit.
            </p>

            @error('usage_limit')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="min_order_amount">
                Minimum order amount
            </label>

            <input
                id="min_order_amount"
                name="min_order_amount"
                type="number"
                min="1"
                step="1"
                value="{{ $minOrderAmountValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('min_order_amount') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="1000"
            >

            <p class="mt-1 text-xs text-slate-500">
                Amount in minor units, for example cents. Leave empty for no minimum.
            </p>

            @error('min_order_amount')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="assigned_email">
                Assigned email
            </label>

            <input
                id="assigned_email"
                name="assigned_email"
                type="email"
                value="{{ $assignedEmailValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('assigned_email') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="customer@example.com"
            >

            <p class="mt-1 text-xs text-slate-500">
                Optional. Restricts this code to one customer email.
            </p>

            @error('assigned_email')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="catalog_item_id">
                Catalog item restriction
            </label>

            <select
                id="catalog_item_id"
                name="catalog_item_id"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('catalog_item_id') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >
                <option value="">Any catalog item</option>

                @foreach ($catalogItems as $catalogItem)
                    <option
                        value="{{ $catalogItem->id }}"
                        @selected((string) $catalogItemIdValue === (string) $catalogItem->id)
                    >
                        {{ $catalogItem->title ?? $catalogItem->sku ?? ('Catalog item #' . $catalogItem->id) }}
                    </option>
                @endforeach
            </select>

            <p class="mt-1 text-xs text-slate-500">
                Optional. Restricts this code to a specific catalog item.
            </p>

            @error('catalog_item_id')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="starts_at">
                Starts at
            </label>

            <input
                id="starts_at"
                name="starts_at"
                type="datetime-local"
                value="{{ $startsAtValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('starts_at') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('starts_at')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="expires_at">
                Expires at
            </label>

            <input
                id="expires_at"
                name="expires_at"
                type="datetime-local"
                value="{{ $expiresAtValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('expires_at') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('expires_at')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="internal_note">
            Internal note
        </label>

        <textarea
            id="internal_note"
            name="internal_note"
            rows="3"
            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('internal_note') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            placeholder="Internal campaign notes, support context, or admin-only remarks."
        >{{ $internalNoteValue }}</textarea>

        @error('internal_note')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="mb-3 text-sm font-semibold text-slate-800">
            Availability
        </p>

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <input type="hidden" name="is_active" value="0">

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        id="is_active"
                        name="is_active"
                        type="checkbox"
                        value="1"
                        @checked($isActiveValue)
                        class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    Discount code is active
                </label>

                @error('is_active')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="hidden" name="one_use_per_customer" value="0">

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        id="one_use_per_customer"
                        name="one_use_per_customer"
                        type="checkbox"
                        value="1"
                        @checked($oneUsePerCustomerValue)
                        class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    One use per customer
                </label>

                @error('one_use_per_customer')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <p id="discount-code-form-message" class="text-sm"></p>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
        <a
            href="{{ $cancelUrl }}"
            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex items-center rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/30"
        >
            {{ $submitLabel }}
        </button>
    </div>
</form>

@if ($showDisable)
    <div class="mt-6 border-t border-slate-100 pt-5">
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
            <h3 class="text-sm font-semibold text-rose-800">
                Danger zone
            </h3>

            <p class="mt-1 text-sm text-rose-700">
                Disabling this discount code will prevent it from being used at checkout, but it will remain available in admin records.
            </p>

            <form
                action="{{ $disableUrl }}"
                method="POST"
                class="mt-4"
                onsubmit="return confirm('Are you sure you want to disable this discount code?')"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    id="discount-code-disable"
                    class="rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                >
                    Disable discount code
                </button>
            </form>
        </div>
    </div>
@endif