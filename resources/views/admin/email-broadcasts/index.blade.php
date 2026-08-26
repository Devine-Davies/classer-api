@extends('admin.layout')

@php
    $activeSection = 'email-broadcasts';
    $emailBroadcastResult = session('emailBroadcastResult');
    $selectedTemplateKey = (string) old('template', '');
    $templateGroups = collect($broadcastTemplates)
        ->map(fn($template, $key) => array_merge($template, ['key' => $key, 'category' => $template['category'] ?? 'Other']))
        ->groupBy('category');
    $selectedTemplate = $selectedTemplateKey !== '' ? ($broadcastTemplates[$selectedTemplateKey] ?? null) : null;
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Email Broadcast Templates</h2>
        <p class="mt-[0.35rem] text-admin-muted">Select a template and queue a broadcast for a list of user addresses.</p>
    </header>

    <section class="border border-admin-stroke bg-white">
        <div class="border-b border-[#e5edf3] bg-[#fbfdff] px-5 py-4">
            <h2 class="m-0 text-base font-bold text-admin-ink">Send Email Broadcast</h2>
        </div>

        <div class="px-5 py-5">

            @if (is_array($emailBroadcastResult))
                <div class="mb-4 space-y-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <p class="font-semibold">
                        Queued {{ (int) ($emailBroadcastResult['total_sent'] ?? 0) }} emails using "{{ data_get($emailBroadcastResult, 'template.label', '-') }}".
                    </p>

                    @if (! empty($emailBroadcastResult['not_found'] ?? []))
                        <p>Not found: {{ implode(', ', $emailBroadcastResult['not_found']) }}</p>
                    @endif

                    @if (! empty($emailBroadcastResult['ineligible'] ?? []))
                        <p>Not eligible for this template: {{ implode(', ', $emailBroadcastResult['ineligible']) }}</p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.email-broadcasts.queue') }}" class="space-y-4">
                @csrf

                <fieldset class="rounded-[0.65rem] border border-[#d8e2ea] bg-[#fbfdff] p-4">
                    <legend class="px-1 text-sm font-semibold text-admin-ink">Template Selection</legend>
                    <label for="template" class="mb-2 block text-sm font-medium text-admin-ink">
                        Email Template
                    </label>
                    <select id="template" name="template" required
                        class="w-full rounded-[0.65rem] border border-[#d8e2ea] bg-white px-3 py-2.5 text-sm text-admin-ink shadow-sm focus:border-admin-primary focus:outline-none focus:ring-4 focus:ring-admin-primary/10">
                        <option value="" disabled {{ old('template') ? '' : 'selected' }}>Select a template</option>
                        @foreach ($templateGroups as $groupName => $groupTemplates)
                            <optgroup label="{{ $groupName }}">
                                @foreach ($groupTemplates as $template)
                                    <option value="{{ $template['key'] }}" @selected($selectedTemplateKey === $template['key'])>
                                        {{ $template['label'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="mt-2 text-[0.75rem] text-admin-muted">
                        The selected template controls which users are eligible for sending.
                    </p>
                    @if (is_array($selectedTemplate) && ! empty($selectedTemplate['description']))
                        <p class="mt-2 text-[0.75rem] text-admin-primary">
                            {{ $selectedTemplate['description'] }}
                        </p>
                    @endif
                </fieldset>

                <div>
                    <label for="emails" class="mb-2 block text-sm font-medium text-admin-ink">
                        Email Addresses (separated by commas)
                    </label>
                    <textarea id="emails" name="emails" rows="4" required
                        class="w-full rounded-[0.65rem] border border-[#d8e2ea] bg-white px-3 py-2.5 text-sm text-admin-ink shadow-sm placeholder:text-slate-400 focus:border-admin-primary focus:outline-none focus:ring-4 focus:ring-admin-primary/10"
                        placeholder="user1@example.com, user2@example.com">{{ old('emails') }}</textarea>
                    <p class="mt-2 text-[0.75rem] text-admin-muted">
                        Tip: you can paste a list; commas and line breaks are both OK.
                    </p>
                </div>

                <button type="submit"
                    class="btn-outline-invert inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold">
                    <span>Queue Broadcast</span>
                </button>
            </form>
        </div>
    </section>
@endsection