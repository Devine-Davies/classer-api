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
                class="btn-outline-invert inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f766e] focus:ring-offset-2"
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
                        <th class="{{ $thClass }}">Method</th>
                        <th class="{{ $thClass }}">Cost</th>
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
                                    <span class="pill amber">Unpublished</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">{{ $item['_shipping_method'] ?? 'Standard' }}</td>
                            <td class="{{ $tdClass }}">{{ $item['_shipping_cost'] ?? 0 }}</td>
                            <td class="{{ $tdClass }} text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a
                                        href="{{ route('admin.shipping.edit', ['shippingRow' => $item['_row']]) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-[#d9e4ec] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#334155] transition hover:border-[#94a3b8] hover:bg-[#f8fafc]"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.shipping.destroy', ['shippingRow' => $item['_row']]) }}" onsubmit="return confirm('Delete this shipping row? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirmDelete" value="DELETE">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @include('admin.partials.table-empty', [
                                'colspan' => 7,
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
