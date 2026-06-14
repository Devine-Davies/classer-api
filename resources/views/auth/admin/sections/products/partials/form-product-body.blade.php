@php
    $product = $product ?? $entity ?? null;
    $isEdit = $isEdit ?? false;

    $codeValue = old('code', $product->code ?? str()->uuid()->toString());
    $slugValue = old('slug', $product->slug ?? '');
    $titleValue = old('title', $product->title ?? '');
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

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}" for="slug">
                        Slug <span class="text-rose-600">*</span>
                    </label>

                    <input
                        value="{{ $slugValue }}"
                        id="slug"
                        name="slug"
                        type="text"
                        required
                        placeholder="cloud-share"
                        class="{{ $inputBaseClass }} {{ $errors->has('slug') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    >

                    <p class="{{ $helpClass }}">
                        URL-friendly identifier for this product.
                    </p>

                    @error('slug')
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