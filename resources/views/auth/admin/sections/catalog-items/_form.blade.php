@php
    $catalogItem = $catalogItem ?? $entity ?? null;

    $isEdit = $isEdit ?? false;

    $formId = $formId ?? 'catalog-item-form';

    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $submitLabel = $submitLabel ?? ($isEdit ? 'Update catalog item' : 'Create catalog item');

    $cancelUrl = $cancelUrl ?? url('/auth/admin/catalog-items');

    $products = $products ?? collect();
    $plans = $plans ?? collect();

    $sellableTypeValue = old('sellable_type', $catalogItem->sellable_type ?? 'App\Models\Product');
    $sellableIdValue = old('sellable_id', $catalogItem->sellable_id ?? '');

    $skuValue = old('sku', $catalogItem->sku ?? '');
    $slugValue = old('slug', $catalogItem->slug ?? '');
    $titleValue = old('title', $catalogItem->title ?? '');
    $priceAmountValue = old('price_amount', $catalogItem->price_amount ?? '');
    $promotionPercentageValue = old('promotion_percentage', $catalogItem->promotion_percentage ?? 0);
    $currencyValue = old('currency', $catalogItem->currency ?? 'USD');
    $imageUrlValue = old('image_url', $catalogItem->image_url ?? '');

    $isActiveValue = old('is_active', $catalogItem->is_active ?? true);
    $promotionEligibleValue = old('promotion_eligible', $catalogItem->promotion_eligible ?? false);
    $discountCodeEligibleValue = old('discount_code_eligible', $catalogItem->discount_code_eligible ?? false);
    $shippingRequiredValue = old('shipping_required', $catalogItem->shipping_required ?? false);
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
            <label class="block text-sm font-medium text-slate-700" for="sellable_type">
                Sellable type <span class="text-rose-600">*</span>
            </label>

            <select
                id="sellable_type"
                name="sellable_type"
                required
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('sellable_type') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >
                <option value="App\Models\Product" @selected($sellableTypeValue === 'App\Models\Product')>
                    Product
                </option>

                <option value="App\Models\Plan" @selected($sellableTypeValue === 'App\Models\Plan')>
                    Plan
                </option>
            </select>

            @error('sellable_type')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="sellable_id">
                Sellable record <span class="text-rose-600">*</span>
            </label>

            <select
                id="sellable_id"
                name="sellable_id"
                required
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('sellable_id') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >
                <option value="">Select a sellable record</option>

                <optgroup label="Products" data-sellable-group="App\Models\Product">
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-sellable-type="App\Models\Product"
                            @selected($sellableTypeValue === 'App\Models\Product' && (string) $sellableIdValue === (string) $product->id)
                        >
                            {{ $product->name ?? $product->title ?? $product->sku ?? ('Product #' . $product->id) }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="Plans" data-sellable-group="App\Models\Plan">
                    @foreach ($plans as $plan)
                        <option
                            value="{{ $plan->id }}"
                            data-sellable-type="App\Models\Plan"
                            @selected($sellableTypeValue === 'App\Models\Plan' && (string) $sellableIdValue === (string) $plan->id)
                        >
                            {{ $plan->title ?? $plan->code ?? ('Plan #' . $plan->id) }}
                        </option>
                    @endforeach
                </optgroup>
            </select>

            @error('sellable_id')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="sku">
                SKU <span class="text-rose-600">*</span>
            </label>

            <input
                id="sku"
                name="sku"
                type="text"
                maxlength="64"
                required
                value="{{ $skuValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-sm uppercase text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('sku') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('sku')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="slug">
                Slug <span class="text-rose-600">*</span>
            </label>

            <input
                id="slug"
                name="slug"
                type="text"
                maxlength="255"
                required
                value="{{ $slugValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('slug') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('slug')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="title">
            Title <span class="text-rose-600">*</span>
        </label>

        <input
            id="title"
            name="title"
            type="text"
            maxlength="255"
            required
            value="{{ $titleValue }}"
            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('title')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-slate-700" for="price_amount">
                Price amount <span class="text-rose-600">*</span>
            </label>

            <input
                id="price_amount"
                name="price_amount"
                type="number"
                min="0"
                step="1"
                required
                value="{{ $priceAmountValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('price_amount') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            <p class="mt-1 text-xs text-slate-500">
                Amount in minor units, for example cents.
            </p>

            @error('price_amount')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="promotion_percentage">
                Promotion percentage
            </label>

            <input
                id="promotion_percentage"
                name="promotion_percentage"
                type="number"
                min="0"
                max="100"
                step="1"
                value="{{ $promotionPercentageValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('promotion_percentage') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('promotion_percentage')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700" for="currency">
                Currency <span class="text-rose-600">*</span>
            </label>

            <input
                id="currency"
                name="currency"
                type="text"
                maxlength="3"
                required
                value="{{ $currencyValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm uppercase text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('currency') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
            >

            @error('currency')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="image_url">
            Image URL
        </label>

        <input
            id="image_url"
            name="image_url"
            type="url"
            value="{{ $imageUrlValue }}"
            class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('image_url') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('image_url')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror

        @if ($imageUrlValue)
            <div class="mt-3">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">
                    Current image
                </p>

                <img
                    src="{{ $imageUrlValue }}"
                    alt="{{ $titleValue ?: 'Catalog item image' }}"
                    class="max-h-40 rounded-xl border border-slate-200 bg-slate-50 object-contain p-2"
                >
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="mb-3 text-sm font-semibold text-slate-800">
            Availability and eligibility
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

                    Active
                </label>

                @error('is_active')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="hidden" name="promotion_eligible" value="0">

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        id="promotion_eligible"
                        name="promotion_eligible"
                        type="checkbox"
                        value="1"
                        @checked($promotionEligibleValue)
                        class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    Promotion eligible
                </label>

                @error('promotion_eligible')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="hidden" name="discount_code_eligible" value="0">

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        id="discount_code_eligible"
                        name="discount_code_eligible"
                        type="checkbox"
                        value="1"
                        @checked($discountCodeEligibleValue)
                        class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    Discount code eligible
                </label>

                @error('discount_code_eligible')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="hidden" name="shipping_required" value="0">

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        id="shipping_required"
                        name="shipping_required"
                        type="checkbox"
                        value="1"
                        @checked($shippingRequiredValue)
                        class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    Shipping required
                </label>

                @error('shipping_required')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <p id="catalog-item-form-message" class="text-sm"></p>

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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sellableType = document.getElementById('sellable_type');
        const sellableId = document.getElementById('sellable_id');

        if (!sellableType || !sellableId) {
            return;
        }

        const syncSellableOptions = () => {
            const selectedType = sellableType.value;

            Array.from(sellableId.options).forEach((option) => {
                const optionType = option.dataset.sellableType;

                if (!optionType) {
                    option.hidden = false;
                    return;
                }

                option.hidden = optionType !== selectedType;
            });

            const selectedOption = sellableId.options[sellableId.selectedIndex];

            if (selectedOption && selectedOption.dataset.sellableType && selectedOption.dataset.sellableType !== selectedType) {
                sellableId.value = '';
            }
        };

        sellableType.addEventListener('change', syncSellableOptions);

        syncSellableOptions();
    });
</script>