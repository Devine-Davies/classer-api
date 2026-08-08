@extends('admin.layout')

@php
    $activeSection = 'currencies';
    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="currencies-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="currencies-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="currencies-search" name="q" type="search" placeholder="Search by code, label, or country code"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._currenciesSearchTimer); window._currenciesSearchTimer = setTimeout(() => document.getElementById('currencies-filter-form').submit(), 300)">
                </label>
            </div>

            <a
                href="{{ route('admin.currencies.add') }}"
                class="btn-outline-invert inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold"
            >
                Add currency
            </a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[820px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Code</th>
                        <th class="{{ $thClass }}">Label</th>
                        <th class="{{ $thClass }}">Symbol</th>
                        <th class="{{ $thClass }}">Country</th>
                        <th class="{{ $thClass }}">GBP rate</th>
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }} text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr id="currency-row-{{ $item['_row'] }}" class="scroll-mt-24">
                            <td class="{{ $tdClass }}">
                                <a href="#currency-row-{{ $item['_row'] }}" class="font-semibold text-slate-900 hover:underline focus:underline">
                                    {{ strtoupper($item['code'] ?? '-') }}
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">{{ $item['label'] ?? '-' }}</td>
                            <td class="{{ $tdClass }} text-xl leading-none">{{ $item['symbol'] ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ strtoupper($item['countryCode'] ?? '-') }}</td>
                            <td class="{{ $tdClass }}">{{ number_format((float) ($item['rate'] ?? 0), 4, '.', '') }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($item['_is_published'] ?? true)
                                    <span class="pill emerald">Published</span>
                                @else
                                    <span class="pill slate">Unpublished</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }} text-right">
                                @include('admin.partials.table-actions-dropdown', [
                                    'buttonLabel' => 'Options',
                                    'items' => [
                                        [
                                            'label' => ($item['_is_published'] ?? true) ? 'Unpublish' : 'Publish',
                                            'url' => route('admin.currencies.publish', [
                                                'currencyRow' => $item['_row'],
                                                'q' => $q,
                                            ]),
                                            'method' => 'POST',
                                            'color' => 'amber',
                                        ],
                                        [
                                            'label' => 'Edit',
                                            'url' => route('admin.currencies.edit', ['currencyRow' => $item['_row']]),
                                            'method' => 'GET',
                                            'color' => 'slate',
                                        ],
                                        [
                                            'label' => 'Delete',
                                            'url' => route('admin.currencies.destroy', ['currencyRow' => $item['_row']]),
                                            'method' => 'DELETE',
                                            'color' => 'rose',
                                            'confirm' => 'Delete this currency? This cannot be undone.',
                                            'fields' => [
                                                'confirmDelete' => 'DELETE',
                                            ],
                                        ],
                                    ],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @include('admin.partials.table-empty', [
                                'colspan' => 7,
                                'title' => 'No currencies found',
                                'message' => 'Try a different search or verify that storage/app/public/currencies.json exists.',
                            ])
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection