@php
    $product = $product ?? $entity ?? null;

    $isEdit = $isEdit ?? false;

    $formId = $formId ?? 'product-form';

    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $submitLabel = $submitLabel ?? ($isEdit ? 'Update product' : 'Create product');

    $cancelUrl = $cancelUrl ?? url('/auth/admin/products');

    $deleteUrl = $deleteUrl ?? null;

    $showDelete = $showDelete ?? ($isEdit && $deleteUrl);

    $imageUrl = old('image_url', $product->image_url ?? '');
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

    <div>
        <label class="block text-sm font-medium text-slate-700" for="sku">
            SKU <span class="text-rose-600">*</span>
        </label>

        <input
            value="{{ old('sku', $product->sku ?? '') }}"
            id="sku"
            name="sku"
            type="text"
            maxlength="64"
            required
            class="mt-1 w-full rounded-lg border px-3 py-2 font-mono uppercase focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('sku') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
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
            value="{{ old('slug', $product->slug ?? '') }}"
            id="slug"
            name="slug"
            type="text"
            required
            class="mt-1 w-full rounded-lg border px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('slug') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('slug')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="name">
            Name <span class="text-rose-600">*</span>
        </label>

        <input
            value="{{ old('name', $product->name ?? '') }}"
            id="name"
            name="name"
            type="text"
            required
            class="mt-1 w-full rounded-lg border px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('name') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('name')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="short_description">
            Short description
        </label>

        <input
            value="{{ old('short_description', $product->short_description ?? '') }}"
            id="short_description"
            name="short_description"
            type="text"
            maxlength="255"
            class="mt-1 w-full rounded-lg border px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('short_description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('short_description')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="long_description">
            Long description
        </label>

        <textarea
            id="long_description"
            name="long_description"
            rows="5"
            class="mt-1 w-full rounded-lg border px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('long_description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >{{ old('long_description', $product->long_description ?? '') }}</textarea>

        @error('long_description')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="image_url">
            Image URL
        </label>

        <input
            value="{{ $imageUrl }}"
            id="image_url"
            name="image_url"
            type="url"
            class="mt-1 w-full rounded-lg border px-3 py-2 focus:border-[var(--admin-primary)] focus:outline-none {{ $errors->has('image_url') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
        >

        @error('image_url')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror

        @if ($imageUrl)
            <div class="mt-3">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">
                    Current image
                </p>

                <img
                    src="{{ $imageUrl }}"
                    alt="{{ old('name', $product->name ?? 'Product image') }}"
                    class="max-h-40 rounded-xl border border-slate-200 bg-slate-50 object-contain p-2"
                >
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <input type="hidden" name="is_active" value="0">

        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input
                {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
            >

            Product is active
        </label>

        <p class="mt-1 text-xs text-slate-500">
            Inactive products should not be available for new subscriptions or checkout.
        </p>

        @error('is_active')
            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
        @enderror
    </div>

    <p id="product-form-message" class="text-sm"></p>

    <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="rounded-xl bg-[var(--admin-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
            >
                {{ $submitLabel }}
            </button>

            <a
                href="{{ $cancelUrl }}"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Cancel
            </a>
        </div>
    </div>
</form>

@if ($showDelete)
    <div class="mt-6 border-t border-slate-100 pt-5">
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
            <h3 class="text-sm font-semibold text-rose-800">
                Danger zone
            </h3>

            <p class="mt-1 text-sm text-rose-700">
                Soft deleting this product will remove it from active admin usage without permanently deleting its database record.
            </p>

            <form
                action="{{ $deleteUrl }}"
                method="POST"
                class="mt-4"
                onsubmit="return confirm('Are you sure you want to soft delete this product?')"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    id="product-delete"
                    class="rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                >
                    Soft delete product
                </button>
            </form>
        </div>
    </div>
@endif