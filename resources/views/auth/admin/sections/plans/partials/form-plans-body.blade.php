@php
    $plan = $plan ?? $entity ?? null;

    $titleValue = old('title', $plan->title ?? '');
    $codeValue = old('code', $plan->code ?? '');
    $typeValue = old('type', $plan->type ?? '');
    $quotaValue = old('quota', $plan->quota ?? '');
    $durationValue = old('duration', $plan->duration ?? '');
    $shortDescriptionValue = old('short_description', $plan->short_description ?? '');
    $descriptionValue = old('description', $plan->description ?? '');

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<div class="space-y-6">
    <div class="{{ $sectionClass }}">
        <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="{{ $sectionTitleClass }}">Plan details</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Core subscription plan information used by checkout, subscriptions, and entitlement logic.
                </p>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <label for="title" class="{{ $labelClass }}">
                    Title <span class="text-rose-600">*</span>
                </label>

                <input
                    id="title"
                    name="title"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ $titleValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="Cloud Share - 6 Months"
                >

                <p class="{{ $helpClass }}">
                    Human-readable name shown in admin, checkout, and subscription history.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
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
                @endif

                <div>
                    <label for="type" class="{{ $labelClass }}">
                        Type <span class="text-rose-600">*</span>
                    </label>

                    <input
                        id="type"
                        name="type"
                        type="text"
                        maxlength="120"
                        required
                        value="{{ $typeValue }}"
                        class="{{ $inputBaseClass }} font-mono {{ ($errors->has('type') || $errors->has('type')) ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="cloud_share"
                    >

                    <p class="{{ $helpClass }}">
                        Groups plans by entitlement type, for example cloud_share.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Entitlement limits</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Define the storage quota and subscription length granted by this plan.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="quota" class="{{ $labelClass }}">
                    Quota <span class="text-rose-600">*</span>
                </label>

                <input
                    id="quota"
                    name="quota"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="{{ $quotaValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('quota') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="2147483648"
                >

                <p class="{{ $helpClass }}">
                    Storage quota in bytes. Example: 2147483648 is 2 GB.
                </p>
            </div>

            <div>
                <label for="duration" class="{{ $labelClass }}">
                    Duration
                </label>

                <div class="relative">
                    <input
                        id="duration"
                        name="duration"
                        type="number"
                        min="1"
                        step="1"
                        value="{{ $durationValue }}"
                        class="{{ $inputBaseClass }} pr-14 {{ $errors->has('duration') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="180"
                    >

                    <span class="pointer-events-none absolute right-3 top-[0.85rem] text-sm font-semibold text-slate-400">
                        days
                    </span>
                </div>

                <p class="{{ $helpClass }}">
                    Duration in days. Example: 180 is roughly 6 months.
                </p>
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
</div>