@php
    $item = $item ?? null;
    $action = $action ?? route('admin.currencies');
    $isEdit = $isEdit ?? ($item !== null);
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $codeValue = old('code', $item['code'] ?? '');
    $labelValue = old('label', $item['label'] ?? '');
    $symbolValue = old('symbol', $item['symbol'] ?? '');
    $countryCodeValue = old('countryCode', $item['countryCode'] ?? '');
    $rateValue = old('rate', $item['rate'] ?? 1);
    $isPublishedValue = old('is_published', (int) ($item['_is_published'] ?? 1));

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
            <h3 class="{{ $sectionTitleClass }}">Currency details</h3>
            <p class="{{ $sectionDescriptionClass }}">
                These values power public currency selection and GBP-based checkout conversion.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}" for="code">
                    Code <span class="text-rose-600">*</span>
                </label>

                <input
                    id="code"
                    name="code"
                    type="text"
                    maxlength="3"
                    required
                    value="{{ $codeValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('code') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="GBP"
                >

                @error('code')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="label">
                    Label <span class="text-rose-600">*</span>
                </label>

                <input
                    id="label"
                    name="label"
                    type="text"
                    maxlength="20"
                    required
                    value="{{ $labelValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('label') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="GBP"
                >

                @error('label')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="symbol">
                    Symbol <span class="text-rose-600">*</span>
                </label>

                <input
                    id="symbol"
                    name="symbol"
                    type="text"
                    maxlength="12"
                    required
                    value="{{ $symbolValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('symbol') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="£"
                >

                @error('symbol')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="countryCode">
                    Country code (ISO2) <span class="text-rose-600">*</span>
                </label>

                <input
                    id="countryCode"
                    name="countryCode"
                    type="text"
                    maxlength="2"
                    required
                    value="{{ $countryCodeValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('countryCode') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="GB"
                >

                @error('countryCode')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="{{ $labelClass }}" for="rate">
                    Rate from GBP <span class="text-rose-600">*</span>
                </label>

                <input
                    id="rate"
                    name="rate"
                    type="number"
                    min="0.000001"
                    step="0.000001"
                    required
                    value="{{ $rateValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('rate') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="1.350000"
                >

                <p class="{{ $helpClass }}">
                    Use 1 for GBP. Other currencies should be the multiplier from one British pound.
                </p>

                @error('rate')
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
                    Show this currency in the public selector
                </label>

                @error('is_published')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="mt-auto flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <a href="{{ route('admin.currencies') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex items-center rounded-xl bg-[var(--admin-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110"
        >
            {{ $isEdit ? 'Save currency' : 'Create currency' }}
        </button>
    </div>
</form>