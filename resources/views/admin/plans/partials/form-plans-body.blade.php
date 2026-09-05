@php
    $plan = $plan ?? $entity ?? null;

    $titleValue = old('title', $plan->title ?? '');
    $codeValue = old('code', $plan->code ?? '');
    $durationValue = old('duration', $plan->duration ?? '');
    $shortDescriptionValue = old('short_description', $plan->short_description ?? '');
    $descriptionValue = old('description', $plan->description ?? '');
    $entitlementsByCapability = collect(data_get($plan, 'entitlements', []))
        ->keyBy(fn ($entitlement) => data_get($entitlement, 'capability'));
    $cloudShareEnabled = (bool) old('entitlements.cloud_share.enabled', $entitlementsByCapability->has('cloud_share'));
    $cloudShareQuota = old('entitlements.cloud_share.quota', data_get($entitlementsByCapability->get('cloud_share'), 'quota', ''));
    $cloudBackupEnabled = (bool) old('entitlements.cloud_backup.enabled', $entitlementsByCapability->has('cloud_backup'));
    $cloudBackupQuota = old('entitlements.cloud_backup.quota', data_get($entitlementsByCapability->get('cloud_backup'), 'quota', ''));

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

            <div>
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
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Entitlement limits</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Select the cloud capabilities granted by this plan and set each storage quota.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ([
                'cloud_share' => ['label' => 'Cloud Share', 'enabled' => $cloudShareEnabled, 'quota' => $cloudShareQuota],
                'cloud_backup' => ['label' => 'Cloud Backup', 'enabled' => $cloudBackupEnabled, 'quota' => $cloudBackupQuota],
            ] as $capability => $entitlement)
                <div class="rounded-lg border border-slate-200 p-4">
                    <input type="hidden" name="entitlements[{{ $capability }}][enabled]" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <input
                            type="checkbox"
                            name="entitlements[{{ $capability }}][enabled]"
                            value="1"
                            class="rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                            @checked($entitlement['enabled'])
                        >
                        {{ $entitlement['label'] }}
                    </label>

                    <label for="entitlement-{{ $capability }}-quota" class="{{ $labelClass }} mt-4">
                        Quota in bytes
                    </label>
                    <input
                        id="entitlement-{{ $capability }}-quota"
                        name="entitlements[{{ $capability }}][quota]"
                        type="number"
                        min="1"
                        step="1"
                        value="{{ $entitlement['quota'] }}"
                        class="{{ $inputBaseClass }} {{ $errors->has("entitlements.{$capability}.quota") ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="1073741824"
                    >
                    <p class="{{ $helpClass }}">1073741824 bytes is 1 GB.</p>
                    @error("entitlements.{$capability}.quota")
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-5 max-w-md">
            <div>
                <label for="duration" class="{{ $labelClass }}">Duration</label>

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

                    <span class="pointer-events-none absolute right-3 top-[0.85rem] text-sm font-semibold text-slate-400">days</span>
                </div>

                <p class="{{ $helpClass }}">Duration in days. Example: 180 is roughly 6 months.</p>
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