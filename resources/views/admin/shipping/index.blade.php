@extends('admin.layout')

@php
    $activeSection = 'shipping';
    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="shipping-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="shipping-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="shipping-search" name="q" type="search" placeholder="Search by name, zone, or country code"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._shippingSearchTimer); window._shippingSearchTimer = setTimeout(() => document.getElementById('shipping-filter-form').submit(), 300)">
                </label>
            </div>

            <a
                href="{{ route('admin.shipping.add') }}"
                class="btn-outline-invert inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold"
            >
                Add shipping row
            </a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[880px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Country</th>
                        <th class="{{ $thClass }}">RM code</th>
                        <th class="{{ $thClass }}">ISO3 code</th>
                        <th class="{{ $thClass }}">Postage zone</th>
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Rates</th>
                        <th class="{{ $thClass }}">Checkout default</th>
                        <th class="{{ $thClass }} text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="{{ $tdClass }}">
                                <span class="font-semibold text-slate-900">{{ $item['displayName'] ?? '-' }}</span>
                            </td>
                            <td class="{{ $tdClass }}">{{ $item['rmCountryCode'] ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ $item['countryCode'] ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ $item['postageZone'] ?? '-' }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($item['_is_published'] ?? true)
                                    <span class="pill emerald">Published</span>
                                @else
                                    <span class="pill slate">Unpublished</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @php
                                    $rates = collect($item['_shipping_rates'] ?? [])
                                        ->filter(fn ($rate) => is_array($rate))
                                        ->values();
                                    $ratesCount = $rates->count();
                                    $vendorCount = $rates->pluck('vendor_id')->filter()->unique()->count();
                                    $costs = $rates->pluck('cost')->map(fn ($cost) => max(0, (int) $cost));
                                    $minCost = $costs->min();
                                    $maxCost = $costs->max();
                                @endphp

                                @if ($ratesCount === 0)
                                    <span class="text-slate-500">No rates</span>
                                @else
                                    <div class="text-slate-900 font-medium">{{ $ratesCount }} {{ $ratesCount === 1 ? 'rate' : 'rates' }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $vendorCount }} {{ $vendorCount === 1 ? 'vendor' : 'vendors' }}
                                        @if ($minCost !== null && $maxCost !== null)
                                            ·
                                            @if ($minCost === $maxCost)
                                                Cost {{ $minCost }}
                                            @else
                                                Cost {{ $minCost }}-{{ $maxCost }}
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                <div class="text-slate-900 font-medium">{{ $item['_shipping_method'] ?? 'Standard' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">Cost {{ $item['_shipping_cost'] ?? 0 }}</div>
                            </td>
                            <td class="{{ $tdClass }} text-right">
                                @include('admin.partials.table-actions-dropdown', [
                                    'buttonLabel' => 'Options',
                                    'items' => [
                                        [
                                            'label' => ($item['_is_published'] ?? true) ? 'Unpublish' : 'Publish',
                                            'url' => route('admin.shipping.publish', ['shippingRow' => $item['_row']]),
                                            'method' => 'POST',
                                            'color' => 'amber',
                                        ],
                                        [
                                            'label' => 'Edit',
                                            'url' => route('admin.shipping.edit', ['shippingRow' => $item['_row']]),
                                            'method' => 'GET',
                                            'color' => 'slate',
                                        ],
                                        [
                                            'label' => 'Delete',
                                            'url' => route('admin.shipping.destroy', ['shippingRow' => $item['_row']]),
                                            'method' => 'DELETE',
                                            'color' => 'rose',
                                            'confirm' => 'Delete this shipping row? This cannot be undone.',
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
                                'colspan' => 8,
                                'title' => 'No shipping rows found',
                                'message' => 'Try a different search or verify that storage/app/public/shipping.json exists.',
                            ])
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
