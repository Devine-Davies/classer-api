@php
    $product = $product ?? $entity ?? null;
    $isEdit = $isEdit ?? false;

    $titleValue = old('title', $product->title ?? '');
    $createdAtValue = isset($product->created_at) ? \Illuminate\Support\Carbon::parse($product->created_at)->format('d M Y, H:i') : '';
    $codeValue = old('code', $product->code ?? '');
    $shortDescriptionValue = old('short_description', $product->short_description ?? '');
    $descriptionValue = old('description', $product->description ?? '');

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<section class="space-y-6">
    <div class="{{ $sectionClass }}">
        <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="{{ $sectionTitleClass }}">Product details</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Core product information used internally, in subscriptions, and during checkout.
                </p>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <label class="{{ $labelClass }}" for="title">
                    Title <span class="text-rose-600">*</span>
                </label>

                <input
                    value="{{ $titleValue }}"
                    id="title"
                    name="title"
                    type="text"
                    required
                    placeholder="Cloud Share"
                    class="{{ $inputBaseClass }} {{ $errors->has('title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Display name for this product.
                </p>

                @error('title')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid mt-3 gap-5 md:grid-cols-2">
            @if($isEdit)
                <div>
                    <label class="{{ $labelClass }}" for="code">
                        Code
                    </label>

                    <input
                        id="code"
                        name="code"
                        type="text"
                        value="{{ $codeValue }}"
                        class="{{ $inputBaseClass }} font-mono bg-slate-100 cursor-not-allowed {{ $errors->has('code') ? 'border-rose-300' : 'border-slate-300' }}"
                        readonly
                    >

                    <p class="{{ $helpClass }}">
                        Internal system code for plan lookup and business logic. Cannot be changed after creation.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="createdAt">
                        Date created
                    </label>

                    <input
                        id="createdAt"
                        name="createdAt"
                        type="text"
                        value="{{ $createdAtValue }}"
                        class="{{ $inputBaseClass }} font-mono bg-slate-100 cursor-not-allowed {{ $errors->has('createdAt') ? 'border-rose-300' : 'border-slate-300' }}"
                        readonly
                    >

                    <p class="{{ $helpClass }}">
                        Date and time when this product was created. Cannot be changed after creation.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Descriptions</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Short and long copy used to explain this product to customers or admins.
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label class="{{ $labelClass }}" for="short_description">
                    Short description
                </label>

                <input
                    value="{{ $shortDescriptionValue }}"
                    id="short_description"
                    name="short_description"
                    type="text"
                    maxlength="255"
                    placeholder="Short summary shown in catalog or checkout."
                    class="{{ $inputBaseClass }} {{ $errors->has('short_description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >

                <p class="{{ $helpClass }}">
                    Maximum 255 characters.
                </p>

                @error('short_description')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Detailed product description."
                    class="{{ $inputBaseClass }} resize-y {{ $errors->has('description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                >{{ $descriptionValue }}</textarea>

                @error('description')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</section>