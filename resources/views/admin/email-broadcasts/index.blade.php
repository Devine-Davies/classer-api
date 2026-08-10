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

    <div class="border border-admin-stroke bg-white">
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h2 class="text-2xl font-bold mb-4">Send Email Broadcast</h2>

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

                <fieldset class="rounded-lg border border-gray-200 bg-gray-50/60 p-4">
                    <legend class="px-1 text-sm font-semibold text-gray-700">Template Selection</legend>
                    <label for="template" class="block mb-2 text-sm font-medium">
                        Email Template
                    </label>
                    <select id="template" name="template" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                    <p class="mt-2 text-xs text-gray-500">
                        The selected template controls which users are eligible for sending.
                    </p>
                    @if (is_array($selectedTemplate) && ! empty($selectedTemplate['description']))
                        <p class="mt-2 text-xs text-blue-700">
                            {{ $selectedTemplate['description'] }}
                        </p>
                    @endif
                </fieldset>

                <div>
                    <label for="emails" class="block mb-2 text-sm font-medium">
                        Email Addresses (separated by commas)
                    </label>
                    <textarea id="emails" name="emails" rows="4" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                        placeholder="user1@example.com, user2@example.com">{{ old('emails') }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        Tip: you can paste a list; commas and line breaks are both OK.
                    </p>
                </div>

                <button type="submit"
                    class="inline-flex justify-center items-center py-2 px-4 text-base font-medium text-center text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span>Queue Broadcast</span>
                </button>
            </form>
        </div>
    </div>
@endsection