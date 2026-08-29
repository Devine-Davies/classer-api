@extends('admin.layout')

@php
    $activeSection = 'email-broadcasts';
    $emailBroadcastResult = session('emailBroadcastResult');
    $selectedTemplateKey = (string) old('template', $prefilledTemplate ?? '');
    $emailAddresses = (string) old('emails', $prefilledEmails ?? '');
    $templateGroups = collect($broadcastTemplates)
        ->map(fn ($template, $key) => array_merge($template, [
            'key' => $key,
            'category' => $template['category'] ?? 'Other',
        ]))
        ->groupBy('category');
    $templateDetails = collect($broadcastTemplates)->mapWithKeys(fn ($template, $key) => [
        $key => [
            'label' => $template['label'] ?? $key,
            'description' => $template['description'] ?? '',
        ],
    ]);
@endphp

@section('content')
    <div class="w-full max-w-[920px]">
        <header class="mb-5">
            <h1 class="m-0 text-2xl font-bold text-admin-ink">Email Broadcast</h1>
            <p class="mt-1 text-sm text-admin-muted">Send a templated email to selected users.</p>
        </header>

        @if (is_array($emailBroadcastResult))
            <section class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <p class="font-semibold">
                    Queued {{ (int) ($emailBroadcastResult['total_sent'] ?? 0) }} emails using “{{ data_get($emailBroadcastResult, 'template.label', '—') }}”.
                </p>
                @if (! empty($emailBroadcastResult['not_found'] ?? []))
                    <p class="mt-1 text-amber-800">Not found: {{ implode(', ', $emailBroadcastResult['not_found']) }}</p>
                @endif
                @if (! empty($emailBroadcastResult['ineligible'] ?? []))
                    <p class="mt-1 text-amber-800">Not eligible: {{ implode(', ', $emailBroadcastResult['ineligible']) }}</p>
                @endif
            </section>
        @endif

        <form
            method="POST"
            action="{{ route('admin.email-broadcasts.queue') }}"
            class="space-y-4"
            x-data="{
                template: @js($selectedTemplateKey),
                emails: @js($emailAddresses),
                templates: @js($templateDetails),
                eligible: null,
                notFound: 0,
                ineligible: 0,
                checking: false,
                previewRequest: 0,
                init() {
                    if (this.template && this.validRecipientCount > 0) this.previewEligibility();
                },
                get parsedEmails() {
                    return [...new Set(this.emails.split(/[\s,]+/).map(email => email.trim().toLowerCase()).filter(Boolean))];
                },
                get validEmails() {
                    return this.parsedEmails.filter(email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));
                },
                get validRecipientCount() {
                    return this.validEmails.length;
                },
                get invalidRecipientCount() {
                    return this.parsedEmails.length - this.validRecipientCount;
                },
                get selectedTemplate() {
                    return this.templates[this.template] ?? null;
                },
                get canSubmit() {
                    return this.template && this.validRecipientCount > 0 && this.invalidRecipientCount === 0 && this.eligible !== 0;
                },
                async previewEligibility() {
                    const requestId = ++this.previewRequest;
                    this.eligible = null;
                    this.notFound = 0;
                    this.ineligible = 0;

                    if (!this.template || this.validRecipientCount === 0 || this.invalidRecipientCount > 0) return;

                    this.checking = true;
                    try {
                        const response = await fetch(@js(route('admin.email-broadcasts.preview')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                            body: JSON.stringify({ template: this.template, emails: this.emails }),
                        });
                        if (!response.ok) throw new Error('Preview failed');
                        const result = await response.json();
                        if (requestId !== this.previewRequest) return;
                        this.eligible = result.eligible;
                        this.notFound = result.not_found;
                        this.ineligible = result.ineligible;
                    } catch (error) {
                        if (requestId === this.previewRequest) this.eligible = null;
                    } finally {
                        if (requestId === this.previewRequest) this.checking = false;
                    }
                }
            }"
        >
            @csrf

            <section class="rounded-lg border border-[#dce6ef] bg-white shadow-sm">
                <div class="border-b border-[#edf2f6] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">1</span>
                        <h2 class="text-base font-bold text-[#0f172a]">Choose template</h2>
                    </div>
                </div>
                <div class="px-5 py-5">
                    <label for="template" class="mb-2 block text-sm font-semibold text-[#334155]">Email template</label>
                    <select
                        id="template"
                        name="template"
                        required
                        x-model="template"
                        x-on:change="previewEligibility"
                        class="w-full rounded-lg border border-[#cfd9e2] bg-white px-3 py-2.5 text-sm text-[#1e293b] shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10"
                    >
                        <option value="" disabled>Select a template</option>
                        @foreach ($templateGroups as $groupName => $groupTemplates)
                            <optgroup label="{{ $groupName }}">
                                @foreach ($groupTemplates as $template)
                                    <option value="{{ $template['key'] }}">{{ $template['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-[#718096]" x-text="selectedTemplate?.description || 'Template determines recipient eligibility.'"></p>
                    @error('template')<p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="rounded-lg border border-[#dce6ef] bg-white shadow-sm">
                <div class="border-b border-[#edf2f6] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">2</span>
                        <h2 class="text-base font-bold text-[#0f172a]">Recipients</h2>
                    </div>
                </div>
                <div class="px-5 py-5">
                    <label for="emails" class="mb-2 block text-sm font-semibold text-[#334155]">Email addresses</label>
                    <textarea
                        id="emails"
                        name="emails"
                        rows="4"
                        required
                        x-model="emails"
                        x-on:input.debounce.450ms="previewEligibility"
                        class="w-full resize-y rounded-lg border border-[#cfd9e2] bg-white px-3 py-2.5 text-sm leading-6 text-[#1e293b] shadow-sm placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10"
                        placeholder="user1@example.com&#10;user2@example.com"
                    ></textarea>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <p class="font-semibold text-[#64748b]"><span x-text="validRecipientCount"></span> valid <span x-text="validRecipientCount === 1 ? 'recipient' : 'recipients'"></span></p>
                        <p x-show="invalidRecipientCount > 0" x-cloak class="font-semibold text-rose-700"><span x-text="invalidRecipientCount"></span> invalid</p>
                    </div>
                    @error('emails')<p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                    @error('emails.*')<p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="rounded-lg border border-[#dce6ef] bg-white shadow-sm">
                <div class="border-b border-[#edf2f6] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">3</span>
                        <h2 class="text-base font-bold text-[#0f172a]">Review</h2>
                    </div>
                </div>
                <div class="px-5 py-5">
                    <dl class="grid grid-cols-[8rem_1fr] gap-x-5 gap-y-3 text-sm">
                        <dt class="text-[#64748b]">Template</dt>
                        <dd class="font-semibold text-[#1e293b]" x-text="selectedTemplate?.label || 'Not selected'"></dd>
                        <dt class="text-[#64748b]">Recipients</dt>
                        <dd class="font-semibold text-[#1e293b]" x-text="validRecipientCount"></dd>
                        <dt class="text-[#64748b]">Eligible</dt>
                        <dd class="font-semibold text-[#1e293b]" x-text="checking ? 'Checking…' : (eligible === null ? '—' : eligible)"></dd>
                    </dl>

                    <div x-show="!checking && eligible !== null && (notFound > 0 || ineligible > 0)" x-cloak class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        <span x-show="notFound > 0"><span x-text="notFound"></span> not found.</span>
                        <span x-show="ineligible > 0"><span x-text="ineligible"></span> not eligible for this template.</span>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button
                            type="submit"
                            x-bind:disabled="!canSubmit || checking"
                            class="admin-btn admin-btn-primary"
                        >
                            Queue broadcast
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection