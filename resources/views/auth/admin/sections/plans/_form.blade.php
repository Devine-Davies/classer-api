@php
    $plan = $plan ?? $entity ?? null;

    $isEdit = $isEdit ?? false;

    $formId = $formId ?? 'plan-form';

    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $submitLabel = $submitLabel ?? ($isEdit ? 'Update plan' : 'Create plan');

    $cancelUrl = $cancelUrl ?? url('/auth/admin/plans');

    $titleValue = old('title', $plan->title ?? '');
    $codeValue = old('code', $plan->code ?? '');
    $planTypeValue = old('plan_type', $plan->plan_type ?? $plan->type ?? '');
    $quotaValue = old('quota', $plan->quota ?? '');
    $durationValue = old('duration', $plan->duration ?? '');
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

    @if ($isEdit)
        <input type="hidden" name="plan_uid" value="{{ old('plan_uid', $plan->uid ?? '') }}">
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="title" class="block text-sm font-medium text-slate-700">
                Title <span class="text-rose-600">*</span>
            </label>

            <input
                id="title"
                name="title"
                type="text"
                maxlength="255"
                required
                value="{{ $titleValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-[var(--admin-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/20 {{ $errors->has('title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="Cloud Share - 6 Months"
            >

            @error('title')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code" class="block text-sm font-medium text-slate-700">
                Code <span class="text-rose-600">*</span>
            </label>

            <input
                id="code"
                name="code"
                type="text"
                maxlength="120"
                required
                value="{{ $codeValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-sm text-slate-900 shadow-sm transition focus:border-[var(--admin-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/20 {{ $errors->has('code') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="cloud_share_6_months"
            >

            <p class="mt-1 text-xs text-slate-500">
                Internal plan code used by the system.
            </p>

            @error('code')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="plan_type" class="block text-sm font-medium text-slate-700">
                Type <span class="text-rose-600">*</span>
            </label>

            <input
                id="plan_type"
                name="plan_type"
                type="text"
                maxlength="120"
                required
                value="{{ $planTypeValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-sm text-slate-900 shadow-sm transition focus:border-[var(--admin-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/20 {{ ($errors->has('plan_type') || $errors->has('type')) ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="cloud_share"
            >

            @error('plan_type')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror

            @error('type')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="quota" class="block text-sm font-medium text-slate-700">
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
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-[var(--admin-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/20 {{ $errors->has('quota') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="2147483648"
            >

            <p class="mt-1 text-xs text-slate-500">
                Storage quota in bytes.
            </p>

            @error('quota')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="duration" class="block text-sm font-medium text-slate-700">
                Duration <span class="text-rose-600">*</span>
            </label>

            <input
                id="duration"
                name="duration"
                type="number"
                min="1"
                step="1"
                required
                value="{{ $durationValue }}"
                class="mt-1 w-full rounded-lg border px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-[var(--admin-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)]/20 {{ $errors->has('duration') ? 'border-rose-300 bg-rose-50' : 'border-slate-300' }}"
                placeholder="180"
            >

            <p class="mt-1 text-xs text-slate-500">
                Duration in days. For example, 180 for roughly 6 months.
            </p>

            @error('duration')
                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p id="plan-form-message" class="text-sm"></p>

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