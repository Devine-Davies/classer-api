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

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

@if (session('success'))
    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
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
    class="space-y-6"
    action="{{ $action }}"
    method="POST"
>
    @csrf

    @if (! in_array($method, ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="{{ $sectionClass }}">
        <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="{{ $sectionTitleClass }}">Discount details</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Configure the public code and percentage discount applied at checkout.
                </p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-[1fr_180px]">
            <div>
                <label class="{{ $labelClass }}" for="code">
                    Code <span class="text-rose-600">*</span>
                </label>

                <input
                    id="code"
                    name="code"
                    type="text"
                    maxlength="120"
                    required
                    value="{{ $codeValue }}"
                    class="{{ $inputBaseClass }} font-mono uppercase tracking-wide {{ $errors->has('code') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="WELCOME20"
                >

                <p class="{{ $helpClass }}">
                    Customer-facing code entered at checkout.
                </p>

                @error('code')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="discount_percentage">
                    Discount <span class="text-rose-600">*</span>
                </label>

                <div class="relative">
                    <input
                        id="discount_percentage"
                        name="discount_percentage"
                        type="number"
                        min="1"
                        max="99"
                        step="1"
                        required
                        value="{{ $discountPercentageValue }}"
                        class="{{ $inputBaseClass }} pr-10 {{ $errors->has('discount_percentage') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="20"
                    >

                    <span class="pointer-events-none absolute right-3 top-[0.85rem] text-sm font-semibold text-slate-400">
                        %
                    </span>
                </div>

                <p class="{{ $helpClass }}">
                    Between 1 and 99.
                </p>

                @error('discount_percentage')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Limits and restrictions</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Optionally restrict how often this code can be used, who can use it, or what it applies to.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}" for="usage_limit">
                    Usage limit
                </label>

                <input
                    id="usage_limit"
                    name="usage_limit"
                    type="number"
                    min="1"
                    step="1"
                    value="{{ $usageLimitValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('usage_limit') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="100"
                >

                <p class="{{ $helpClass }}">
                    Leave empty for unlimited total uses.
                </p>

                @error('usage_limit')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="min_order_amount">
                    Minimum order amount
                </label>

                <input
                    id="min_order_amount"
                    name="min_order_amount"
                    type="number"
                    min="1"
                    step="1"
                    value="{{ $minOrderAmountValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('min_order_amount') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="1000"
                >

                <p class="{{ $helpClass }}">
                    Minor units. Example: 1000 = 10.00. Leave empty for no minimum.
                </p>

                @error('min_order_amount')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="assigned_email">
                    Assigned email
                </label>

                <input
                    id="assigned_email"
                    name="assigned_email"
                    type="email"
                    value="{{ $assignedEmailValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('assigned_email') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="customer@example.com"
                >

                <p class="{{ $helpClass }}">
                    Optional. Restricts this code to one customer email.
                </p>

                @error('assigned_email')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="catalog_item_id">
                    Catalog item restriction
                </label>

                <select
                    id="catalog_item_id"
                    name="catalog_item_id"
                    class="{{ $inputBaseClass }} {{ $errors->has('catalog_item_id') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >
                    <option value="">Any catalog item</option>

                    @foreach ($catalogItems as $catalogItem)
                        <option
                            value="{{ $catalogItem->uid }}"
                            @selected((string) $catalogItemIdValue === (string) $catalogItem->uid)
                        >
                            {{ $catalogItem->title ?? $catalogItem->sku ?? ('Catalog item #' . $catalogItem->id) }}
                        </option>
                    @endforeach
                </select>

                <p class="{{ $helpClass }}">
                    Optional. Limits this discount to a specific catalog item.
                </p>

                @error('catalog_item_id')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Availability window</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Schedule when this discount becomes usable and when it expires.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}" for="starts_at">
                    Starts at
                </label>

                <input
                    id="starts_at"
                    name="starts_at"
                    type="datetime-local"
                    value="{{ $startsAtValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('starts_at') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Leave empty to make the code available immediately.
                </p>

                @error('starts_at')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="expires_at">
                    Expires at
                </label>

                <input
                    id="expires_at"
                    name="expires_at"
                    type="datetime-local"
                    value="{{ $expiresAtValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('expires_at') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Leave empty for no expiration date.
                </p>

                @error('expires_at')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Internal notes</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Admin-only context for campaign tracking, support, or operational notes.
            </p>
        </div>

        <div>
            <label class="{{ $labelClass }}" for="internal_note">
                Internal note
            </label>

            <textarea
                id="internal_note"
                name="internal_note"
                rows="4"
                class="{{ $inputBaseClass }} resize-y {{ $errors->has('internal_note') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                placeholder="Internal campaign notes, support context, or admin-only remarks."
            >{{ $internalNoteValue }}</textarea>

            @error('internal_note')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Checkout behavior</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Control whether this code is usable and whether each customer can redeem it more than once.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="is_active" value="0">

                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        id="is_active"
                        name="is_active"
                        type="checkbox"
                        value="1"
                        @checked($isActiveValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">
                            Discount code is active
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Active codes can be validated and redeemed at checkout.
                        </span>
                    </span>
                </label>

                @error('is_active')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="one_use_per_customer" value="0">

                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        id="one_use_per_customer"
                        name="one_use_per_customer"
                        type="checkbox"
                        value="1"
                        @checked($oneUsePerCustomerValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">
                            One use per customer
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Prevents repeat use by the same customer account or email.
                        </span>
                    </span>
                </label>

                @error('one_use_per_customer')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <p id="discount-code-form-message" class="text-sm"></p>

    <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 px-5 py-4 shadow-[0_-10px_25px_rgba(15,23,42,0.06)] backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                Required fields are marked with <span class="font-semibold text-rose-600">*</span>.
            </p>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ $cancelUrl }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-[var(--admin-primary)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/20"
                >
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </div>
</form>

@if ($showDisable)
    <div class="mt-6 border-t border-slate-100 pt-5">
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-rose-800">
                        Danger zone
                    </h3>

                    <p class="mt-1 max-w-2xl text-sm leading-6 text-rose-700">
                        Disabling this discount code prevents it from being used at checkout, but keeps the campaign record available for reporting and audit history.
                    </p>
                </div>

                <form
                    action="{{ $disableUrl }}"
                    method="POST"
                    onsubmit="return confirm('Are you sure you want to disable this discount code?')"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        id="discount-code-disable"
                        class="inline-flex items-center justify-center rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus:outline-none focus:ring-4 focus:ring-rose-200"
                    >
                        Disable discount code
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif