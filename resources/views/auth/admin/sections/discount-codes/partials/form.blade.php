@php
    $discountCode = $discountCode ?? $entity ?? null;

    $isEdit = $isEdit ?? false;
    $formId = $formId ?? 'discount-code-form';
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));
    $submitLabel = $submitLabel ?? ($isEdit ? 'Update discount code' : 'Create discount code');
    $cancelUrl = $cancelUrl ?? url('/auth/admin/discount-codes');
    $catalogItems = $catalogItems ?? collect();
    $codeValue = old('code', $discountCode->code ?? '');

    $discountPercentageValue = old(
        'discountPercentage',
        $discountCode->discountPercentage ?? ''
    );

    $usageLimitValue = old(
        'usageLimit',
        $discountCode->usageLimit ?? ''
    );

    $minOrderAmountValue = old(
        'minOrderAmount',
        $discountCode->minOrderAmount ?? ''
    );

    $assignedEmailValue = old(
        'assignedEmail',
        $discountCode->assignedEmail ?? ''
    );

    $catalogItemIdValue = old(
        'catalogItemId',
        $discountCode->catalogItemId ?? ''
    );

    $internalNoteValue = old(
        'internalNote',
        $discountCode->internalNote ?? ''
    );

    $startsAtRaw = $discountCode->startsAt ?? null;
    $expiresAtRaw = $discountCode->expiresAt ?? null;

    $startsAtValue = old(
        'startsAt', $startsAtRaw ?? '' 
    );

    $expiresAtValue = old(
        'expiresAt', $expiresAtRaw ?? ''
    );

    $isActiveValue = old(
        'isActive',
        $discountCode->isActive ?? false
    );

    $oneUsePerCustomerValue = old(
        'oneUsePerCustomer',
        $discountCode->oneUsePerCustomer ?? false
    );

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mb-4';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
    <form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
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
                    <label class="{{ $labelClass }}" for="discountPercentage">
                        Discount <span class="text-rose-600">*</span>
                    </label>

                    <div class="relative">
                        <input
                            id="discountPercentage"
                            name="discountPercentage"
                            type="number"
                            min="1"
                            max="99"
                            step="1"
                            required
                            value="{{ $discountPercentageValue }}"
                            class="{{ $inputBaseClass }} pr-10 {{ $errors->has('discountPercentage') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                            placeholder="20"
                        >

                        <span class="pointer-events-none absolute right-3 top-[0.85rem] text-sm font-semibold text-slate-400">
                            %
                        </span>
                    </div>

                    <p class="{{ $helpClass }}">
                        Between 1 and 99.
                    </p>

                    @error('discountPercentage')
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
                    <label class="{{ $labelClass }}" for="usageLimit">
                        Usage limit
                    </label>

                    <input
                        id="usageLimit"
                        name="usageLimit"
                        type="number"
                        min="1"
                        step="1"
                        value="{{ $usageLimitValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('usageLimit') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="100"
                    >

                    <p class="{{ $helpClass }}">
                        Leave empty for unlimited total uses.
                    </p>

                    @error('usageLimit')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="minOrderAmount">
                        Minimum order amount
                    </label>

                    <input
                        id="minOrderAmount"
                        name="minOrderAmount"
                        type="number"
                        min="1"
                        step="1"
                        value="{{ $minOrderAmountValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('minOrderAmount') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="1000"
                    >

                    <p class="{{ $helpClass }}">
                        Minor units. Example: 1000 = 10.00. Leave empty for no minimum.
                    </p>

                    @error('minOrderAmount')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="assignedEmail">
                        Assigned email
                    </label>

                    <input
                        id="assignedEmail"
                        name="assignedEmail"
                        type="email"
                        value="{{ $assignedEmailValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('assignedEmail') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="customer@example.com"
                    >

                    <p class="{{ $helpClass }}">
                        Optional. Restricts this code to one customer email.
                    </p>

                    @error('assignedEmail')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="catalogItemId">
                        Catalog item restriction
                    </label>

                    <select
                        id="catalogItemId"
                        name="catalogItemId"
                        class="{{ $inputBaseClass }} {{ $errors->has('catalogItemId') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
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

                    <p class="{{ $helpClass }}">
                        Optional. Limits this discount to a specific catalog item.
                    </p>

                    @error('catalogItemId')
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
                    <label class="{{ $labelClass }}" for="startsAt">
                        Starts at
                    </label>

                    <input
                        id="startsAt"
                        name="startsAt"
                        type="datetime-local"
                        value="{{ $startsAtValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('startsAt') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    >

                    <p class="{{ $helpClass }}">
                        Leave empty to make the code available immediately.
                    </p>

                    @error('startsAt')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="expiresAt">
                        Expires at
                    </label>

                    <input
                        id="expiresAt"
                        name="expiresAt"
                        type="datetime-local"
                        value="{{ $expiresAtValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('expiresAt') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    >

                    <p class="{{ $helpClass }}">
                        Leave empty for no expiration date.
                    </p>

                    @error('expiresAt')
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
                <label class="{{ $labelClass }}" for="internalNote">
                    Internal note
                </label>

                <textarea
                    id="internalNote"
                    name="internalNote"
                    rows="4"
                    class="{{ $inputBaseClass }} resize-y {{ $errors->has('internalNote') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="Internal campaign notes, support context, or admin-only remarks."
                >{{ $internalNoteValue }}</textarea>

                @error('internalNote')
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
                    <input type="hidden" name="isActive" value="0">

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            id="isActive"
                            name="isActive"
                            type="checkbox"
                            value="1"
                            @checked((bool) $isActiveValue)
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

                    @error('isActive')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                    <input type="hidden" name="oneUsePerCustomer" value="0">

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            id="oneUsePerCustomer"
                            name="oneUsePerCustomer"
                            type="checkbox"
                            value="1"
                            @checked((bool) $oneUsePerCustomerValue)
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

                    @error('oneUsePerCustomer')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <p id="discount-code-form-message" class="text-sm"></p>

        <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Required fields are marked with
                    <span class="font-semibold text-rose-600">*</span>.
                </p>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a
                        href="{{ url('/auth/admin/discount-codes') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-green-300 px-4 py-2.5 text-sm font-semibold text-white transition hover:border-slate-300 hover:bg-green-400 hover:text-white"
                    >
                        {{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>