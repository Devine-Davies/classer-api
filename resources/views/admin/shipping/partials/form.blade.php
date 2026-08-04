@php
    $item = $item ?? null;
    $action = $action ?? route('admin.shipping');
    $isEdit = $isEdit ?? ($item !== null);
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $countryCodeValue = old('countryCode', $item['countryCode'] ?? '');
    $rmCountryCodeValue = old('rmCountryCode', $item['rmCountryCode'] ?? '');
    $displayNameValue = old('displayName', $item['displayName'] ?? '');
    $postageZoneValue = old('postageZone', $item['postageZone'] ?? '');
    $postcodeRegexValue = old('postcodeRegex', $item['postcodeRegex'] ?? '');
    $isPublishedValue = old('is_published', (int) ($item['_is_published'] ?? 1));
    $shippingRatesValue = old('shipping_rates', $item['_shipping_rates'] ?? [[
        'vendor_id' => '1',
        'vendor_title' => 'Vendor 1',
        'method' => $item['_shipping_method'] ?? 'Standard',
        'cost' => $item['_shipping_cost'] ?? 0,
        'weight_limit' => $item['_shipping_weight_limit'] ?? 0,
    ]]);

    if (! is_array($shippingRatesValue) || empty($shippingRatesValue)) {
        $shippingRatesValue = [[
            'vendor_id' => '1',
            'vendor_title' => 'Vendor 1',
            'method' => 'Standard',
            'cost' => 0,
            'weight_limit' => 0,
        ]];
    }

    $shippingRatesValue = array_values(array_map(function ($rate): array {
        $rate = is_array($rate) ? $rate : [];

        return [
            'vendor_id' => (string) ($rate['vendor_id'] ?? '1'),
            'vendor_title' => (string) ($rate['vendor_title'] ?? 'Vendor 1'),
            'method' => (string) ($rate['method'] ?? 'Standard'),
            'cost' => (int) ($rate['cost'] ?? 0),
            'weight_limit' => (int) ($rate['weight_limit'] ?? 0),
        ];
    }, $shippingRatesValue));

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mb-4';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
    @csrf

    @if (! in_array($method, ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Country details</h3>
            <p class="{{ $sectionDescriptionClass }}">
                These values are used for checkout validation and country display labels.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}" for="displayName">
                    Display name <span class="text-rose-600">*</span>
                </label>

                <input
                    id="displayName"
                    name="displayName"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ $displayNameValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('displayName') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="United Kingdom"
                >

                @error('displayName')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="postageZone">
                    Postage zone <span class="text-rose-600">*</span>
                </label>

                <input
                    id="postageZone"
                    name="postageZone"
                    type="text"
                    maxlength="120"
                    required
                    value="{{ $postageZoneValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('postageZone') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="EuropeanUnion"
                >

                @error('postageZone')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="rmCountryCode">
                    RM country code (ISO2) <span class="text-rose-600">*</span>
                </label>

                <input
                    id="rmCountryCode"
                    name="rmCountryCode"
                    type="text"
                    maxlength="2"
                    required
                    value="{{ $rmCountryCodeValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('rmCountryCode') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="GB"
                >

                @error('rmCountryCode')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="countryCode">
                    Country code (ISO3) <span class="text-rose-600">*</span>
                </label>

                <input
                    id="countryCode"
                    name="countryCode"
                    type="text"
                    maxlength="3"
                    required
                    value="{{ $countryCodeValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('countryCode') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="GBR"
                >

                @error('countryCode')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="{{ $labelClass }}" for="postcodeRegex">
                    Postcode regex
                </label>

                <input
                    id="postcodeRegex"
                    name="postcodeRegex"
                    type="text"
                    maxlength="255"
                    value="{{ $postcodeRegexValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('postcodeRegex') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="^\\d{4}$"
                >

                <p class="{{ $helpClass }}">
                    Leave blank to remove postcode validation for this row.
                </p>

                @error('postcodeRegex')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="{{ $labelClass }}" for="is_published">
                    Published
                </label>

                <input type="hidden" name="is_published" value="0">

                <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700" for="is_published">
                    <input
                        id="is_published"
                        name="is_published"
                        type="checkbox"
                        value="1"
                        {{ (int) $isPublishedValue === 1 ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >
                    Show this country in checkout
                </label>

                <p class="{{ $helpClass }}">
                    Unpublished shipping rows are hidden from checkout and blocked during validation.
                </p>

                @error('is_published')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Shipping vendors</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Add one or more vendor shipping items. Vendor 1 with method Standard powers checkout shipping today.
            </p>
        </div>

        <div data-shipping-rates-root>
            <div class="space-y-4" data-shipping-rates-list data-next-index="{{ count($shippingRatesValue) }}">
                @foreach ($shippingRatesValue as $index => $rate)
                    <div class="rounded-xl border border-slate-200 p-4" data-shipping-rate-row>
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-800">Vendor item</p>
                            <button
                                type="button"
                                class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                                data-remove-shipping-rate
                            >
                                Remove
                            </button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="{{ $labelClass }}">Vendor ID <span class="text-rose-600">*</span></label>
                                <input
                                    name="shipping_rates[{{ $index }}][vendor_id]"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="16"
                                    required
                                    value="{{ $rate['vendor_id'] }}"
                                    class="{{ $inputBaseClass }} border-slate-300 bg-white"
                                    placeholder="1"
                                >
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Vendor title <span class="text-rose-600">*</span></label>
                                <input
                                    name="shipping_rates[{{ $index }}][vendor_title]"
                                    type="text"
                                    maxlength="120"
                                    required
                                    value="{{ $rate['vendor_title'] }}"
                                    class="{{ $inputBaseClass }} border-slate-300 bg-white"
                                    placeholder="Royal Mail"
                                >
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Method <span class="text-rose-600">*</span></label>
                                <input
                                    name="shipping_rates[{{ $index }}][method]"
                                    type="text"
                                    maxlength="120"
                                    required
                                    value="{{ $rate['method'] }}"
                                    class="{{ $inputBaseClass }} border-slate-300 bg-white"
                                    placeholder="Standard"
                                >
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Cost <span class="text-rose-600">*</span></label>
                                <input
                                    name="shipping_rates[{{ $index }}][cost]"
                                    type="number"
                                    min="0"
                                    step="1"
                                    required
                                    value="{{ $rate['cost'] }}"
                                    class="{{ $inputBaseClass }} border-slate-300 bg-white"
                                    placeholder="0"
                                >
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Weight limit</label>
                                <input
                                    name="shipping_rates[{{ $index }}][weight_limit]"
                                    type="number"
                                    min="0"
                                    step="1"
                                    value="{{ $rate['weight_limit'] }}"
                                    class="{{ $inputBaseClass }} border-slate-300 bg-white"
                                    placeholder="0"
                                >
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button
                type="button"
                class="mt-4 inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                data-add-shipping-rate
            >
                <span aria-hidden="true">+</span>
                Add vendor item
            </button>

            <p class="{{ $helpClass }} mt-3">
                Keep vendor 1 + Standard for checkout shipping. Additional vendors can be stored for future use.
            </p>

            @error('shipping_rates')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <template id="shipping-rate-template">
            <div class="rounded-xl border border-slate-200 p-4" data-shipping-rate-row>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-800">Vendor item</p>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                        data-remove-shipping-rate
                    >
                        Remove
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Vendor ID <span class="text-rose-600">*</span></label>
                        <input
                            name="shipping_rates[{index}][vendor_id]"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="16"
                            required
                            value="{vendorId}"
                            class="{{ $inputBaseClass }} border-slate-300 bg-white"
                            placeholder="1"
                        >
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Vendor title <span class="text-rose-600">*</span></label>
                        <input
                            name="shipping_rates[{index}][vendor_title]"
                            type="text"
                            maxlength="120"
                            required
                            value="{vendorTitle}"
                            class="{{ $inputBaseClass }} border-slate-300 bg-white"
                            placeholder="Royal Mail"
                        >
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Method <span class="text-rose-600">*</span></label>
                        <input
                            name="shipping_rates[{index}][method]"
                            type="text"
                            maxlength="120"
                            required
                            value="{method}"
                            class="{{ $inputBaseClass }} border-slate-300 bg-white"
                            placeholder="Standard"
                        >
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Cost <span class="text-rose-600">*</span></label>
                        <input
                            name="shipping_rates[{index}][cost]"
                            type="number"
                            min="0"
                            step="1"
                            required
                            value="{cost}"
                            class="{{ $inputBaseClass }} border-slate-300 bg-white"
                            placeholder="0"
                        >
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Weight limit</label>
                        <input
                            name="shipping_rates[{index}][weight_limit]"
                            type="number"
                            min="0"
                            step="1"
                            value="{weightLimit}"
                            class="{{ $inputBaseClass }} border-slate-300 bg-white"
                            placeholder="0"
                        >
                    </div>
                </div>
            </div>
        </template>

        <script>
            (() => {
                const root = document.querySelector('[data-shipping-rates-root]');

                if (!root) {
                    return;
                }

                const list = root.querySelector('[data-shipping-rates-list]');
                const addButton = root.querySelector('[data-add-shipping-rate]');

                if (!list || !addButton) {
                    return;
                }

                let nextIndex = Number(list.dataset.nextIndex || list.children.length || 0);

                const bindRemoveHandlers = () => {
                    list.querySelectorAll('[data-remove-shipping-rate]').forEach((button) => {
                        if (button.dataset.boundRemove === '1') {
                            return;
                        }

                        button.dataset.boundRemove = '1';
                        button.addEventListener('click', () => {
                            const rows = list.querySelectorAll('[data-shipping-rate-row]');

                            if (rows.length <= 1) {
                                return;
                            }

                            button.closest('[data-shipping-rate-row]')?.remove();
                        });
                    });
                };

                bindRemoveHandlers();

                addButton.addEventListener('click', () => {
                    const helpers = window.ClasserHelpers || {};
                    const engine = helpers.TemplateEngine || window.TemplateEngine;

                    if (!engine || typeof engine.render !== 'function') {
                        return;
                    }

                    const html = engine.render('shipping-rate-template', {
                        index: String(nextIndex),
                        vendorId: '',
                        vendorTitle: '',
                        method: 'Standard',
                        cost: '0',
                        weightLimit: '0',
                    });

                    list.insertAdjacentHTML('beforeend', html);
                    nextIndex += 1;
                    bindRemoveHandlers();

                    const newestRow = list.querySelector('[data-shipping-rate-row]:last-child input[name$="[vendor_id]"]');
                    newestRow?.focus();
                });
            })();
        </script>
        </div>
    </div>

    <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                Required fields are marked with
                <span class="font-semibold text-rose-600">*</span>.
            </p>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('admin.shipping') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                >
                    Back to list
                </a>

                <button
                    type="submit"
                    class="inline-flex justify-center items-center py-2 px-4 text-base font-medium text-center text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    {{ $isEdit ? 'Update' : 'Create' }}
                </button>
            </div>
        </div>
    </div>
</form>
