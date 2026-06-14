@php
    $catalogItem = $catalogItem ?? $entity ?? null;

    $isEdit = $isEdit ?? false;
    $cancelUrl = $cancelUrl ?? url('/auth/admin/catalog-items');

    $skuValue = old('catalogItem.sku', $catalogItem->sku ?? '');
    $slugValue = old('catalogItem.slug', $catalogItem->slug ?? '');
    $titleValue = old('catalogItem.title', $catalogItem->title ?? '');
    $shortDescriptionValue = old('catalogItem.short_description', $catalogItem->short_description ?? '');
    $descriptionValue = old('catalogItem.description', $catalogItem->description ?? '');
    $priceAmountValue = old('catalogItem.price_amount', $catalogItem->price_amount ?? '');
    $promotionPercentageValue = old('catalogItem.promotion_percentage', $catalogItem->promotion_percentage ?? 0);
    $currencyValue = old('catalogItem.currency', $catalogItem->currency ?? 'GBP');
    $imageUrlValue = old('catalogItem.image_url', $catalogItem->image_url ?? '');

    $isActiveValue = old('catalogItem.is_active', $catalogItem->is_active ?? false);
    $promotionEligibleValue = old('catalogItem.promotion_eligible', $catalogItem->promotion_eligible ?? false);
    $discountCodeEligibleValue = old('catalogItem.discount_code_eligible', $catalogItem->discount_code_eligible ?? false);
    $shippingRequiredValue = old('catalogItem.shipping_required', $catalogItem->shipping_required ?? false);

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<div class="space-y-6">
    <div class="{{ $sectionClass }}">
        <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="{{ $sectionTitleClass }}">Core details</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Basic catalog information shown internally and used by checkout.
                </p>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <label class="{{ $labelClass }}" for="catalogItem_title">
                    Title <span class="text-rose-600">*</span>
                </label>

                <input
                    id="catalogItem_title"
                    name="catalogItem[title]"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ $titleValue }}"
                    placeholder="Cloud Share - 6 Months"
                    class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                @error('catalogItem.title')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}" for="catalogItem_sku">
                        SKU <span class="text-rose-600">*</span>
                    </label>

                    <input
                        id="catalogItem_sku"
                        name="catalogItem[sku]"
                        type="text"
                        maxlength="64"
                        required
                        value="{{ $skuValue }}"
                        placeholder="CLOUD-SHARE-6M"
                        class="{{ $inputBaseClass }} font-mono uppercase {{ $errors->has('catalogItem.sku') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    >

                    <p class="{{ $helpClass }}">
                        Unique stock keeping unit for reporting and checkout mapping.
                    </p>

                    @error('catalogItem.sku')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="catalogItem_slug">
                        Slug <span class="text-rose-600">*</span>
                    </label>

                    <input
                        id="catalogItem_slug"
                        name="catalogItem[slug]"
                        type="text"
                        maxlength="255"
                        required
                        value="{{ $slugValue }}"
                        placeholder="cloud-share-6-months"
                        class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.slug') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    >

                    <p class="{{ $helpClass }}">
                        URL-friendly identifier used by public or checkout routes.
                    </p>

                    @error('catalogItem.slug')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Descriptions</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Short and long copy used to explain this catalog item to customers or admins.
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label class="{{ $labelClass }}" for="catalogItem_short_description">
                    Short description
                </label>

                <input
                    id="catalogItem_short_description"
                    name="catalogItem[short_description]"
                    type="text"
                    maxlength="255"
                    value="{{ $shortDescriptionValue }}"
                    placeholder="Short summary shown in catalog or checkout."
                    class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.short_description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Maximum 255 characters.
                </p>

                @error('catalogItem.short_description')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="catalogItem_description">
                    Description
                </label>

                <textarea
                    id="catalogItem_description"
                    name="catalogItem[description]"
                    rows="6"
                    placeholder="Detailed catalog item description."
                    class="{{ $inputBaseClass }} resize-y {{ $errors->has('catalogItem.description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >{{ $descriptionValue }}</textarea>

                @error('catalogItem.description')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Pricing</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Configure price, currency, and optional promotional percentage.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label class="{{ $labelClass }}" for="catalogItem_price_amount">
                    Price amount <span class="text-rose-600">*</span>
                </label>

                <input
                    id="catalogItem_price_amount"
                    name="catalogItem[price_amount]"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="{{ $priceAmountValue }}"
                    placeholder="1999"
                    class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.price_amount') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Amount in minor units. Example: 1999 = 19.99.
                </p>

                @error('catalogItem.price_amount')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="catalogItem_currency">
                    Currency <span class="text-rose-600">*</span>
                </label>

                <input
                    id="catalogItem_currency"
                    name="catalogItem[currency]"
                    type="text"
                    maxlength="3"
                    required
                    value="{{ $currencyValue }}"
                    placeholder="GBP"
                    class="{{ $inputBaseClass }} uppercase {{ $errors->has('catalogItem.currency') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Three-letter currency code.
                </p>

                @error('catalogItem.currency')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="catalogItem_promotion_percentage">
                    Promotion percentage
                </label>

                <input
                    id="catalogItem_promotion_percentage"
                    name="catalogItem[promotion_percentage]"
                    type="number"
                    min="0"
                    max="100"
                    step="1"
                    value="{{ $promotionPercentageValue }}"
                    placeholder="0"
                    class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.promotion_percentage') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Optional percentage discount, from 0 to 100.
                </p>

                @error('catalogItem.promotion_percentage')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Media</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Optional image used for catalog presentation.
            </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_220px]">
            <div>
                <label class="{{ $labelClass }}" for="catalogItem_image_url">
                    Image URL
                </label>

                <input
                    id="catalogItem_image_url"
                    name="catalogItem[image_url]"
                    type="url"
                    value="{{ $imageUrlValue }}"
                    placeholder="https://example.com/image.jpg"
                    class="{{ $inputBaseClass }} {{ $errors->has('catalogItem.image_url') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Paste a public image URL. Leave empty if this item does not need an image.
                </p>

                @error('catalogItem.image_url')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                    Preview
                </p>

                @if ($imageUrlValue)
                    <img
                        src="{{ $imageUrlValue }}"
                        alt="{{ $titleValue ?: 'Catalog item image' }}"
                        class="mx-auto max-h-36 rounded-xl border border-slate-200 bg-white object-contain p-2"
                    >
                @else
                    <div class="flex h-36 items-center justify-center rounded-xl border border-slate-200 bg-white text-center text-sm text-slate-400">
                        No image URL
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Availability and eligibility</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Control whether this item is active and which checkout features apply.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="catalogItem[is_active]" value="0">

                <label class="flex cursor-pointer items-start gap-3" for="catalogItem_is_active">
                    <input
                        id="catalogItem_is_active"
                        name="catalogItem[is_active]"
                        type="checkbox"
                        value="1"
                        @checked((bool) $isActiveValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Active</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Allows this catalog item to be used in checkout.
                        </span>
                    </span>
                </label>

                @error('catalogItem.is_active')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="catalogItem[promotion_eligible]" value="0">

                <label class="flex cursor-pointer items-start gap-3" for="catalogItem_promotion_eligible">
                    <input
                        id="catalogItem_promotion_eligible"
                        name="catalogItem[promotion_eligible]"
                        type="checkbox"
                        value="1"
                        @checked((bool) $promotionEligibleValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Promotion eligible</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Allows automatic promotion percentage discounts.
                        </span>
                    </span>
                </label>

                @error('catalogItem.promotion_eligible')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="catalogItem[discount_code_eligible]" value="0">

                <label class="flex cursor-pointer items-start gap-3" for="catalogItem_discount_code_eligible">
                    <input
                        id="catalogItem_discount_code_eligible"
                        name="catalogItem[discount_code_eligible]"
                        type="checkbox"
                        value="1"
                        @checked((bool) $discountCodeEligibleValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Discount code eligible</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Allows customer-entered discount codes at checkout.
                        </span>
                    </span>
                </label>

                @error('catalogItem.discount_code_eligible')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="catalogItem[shipping_required]" value="0">

                <label class="flex cursor-pointer items-start gap-3" for="catalogItem_shipping_required">
                    <input
                        id="catalogItem_shipping_required"
                        name="catalogItem[shipping_required]"
                        type="checkbox"
                        value="1"
                        @checked((bool) $shippingRequiredValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Shipping required</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Requires collection of shipping details before purchase.
                        </span>
                    </span>
                </label>

                @error('catalogItem.shipping_required')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>