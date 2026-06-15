@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
@endphp

@section('content')
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="plans-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="plans-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="plans-search" name="q" type="search" placeholder="Search by code, title, or slug"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._discountCodesSearchTimer); window._discountCodesSearchTimer = setTimeout(() => document.getElementById('discount-codes-filter-form').submit(), 300)">
                </label>
            </div>

            <a href="{{ url('/auth/admin/discount-codes/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold text-white">
                Add discount code
            </a>
            <!-- <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                @if ($total)
                    {{ $from }}&ndash;{{ $to }} of {{ number_format($total) }}
                @else
                    0 results
                @endif
            </p> -->
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Code</th>
                        <th class="{{ $thClass }}">Discount</th>
                        <th class="{{ $thClass }}">Catalog Item</th>
                        <th class="{{ $thClass }}">Assigned To</th>
                        <th class="{{ $thClass }}">Usage</th>
                        <th class="{{ $thClass }}">Period</th>
                        <th class="{{ $thClass }}">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $code)
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="{{ route('auth.admin.discount-codes.edit', ['discoCodeUid' => $code->uid]) }}">
                                    <span class="orders-code">{{ $code->code ?? '-' }}</span>
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">
                                <span class="text-sm font-semibold text-slate-900">{{ $code->discount_percentage ?? 0 }}%</span>
                                @if ($code->max_discount_percentage)
                                    <div class="text-xs text-slate-500">Max: {{ $code->max_discount_percentage }}%</div>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($code->catalogItem)
                                    <div class="text-sm font-medium text-slate-900">{{ $code->catalogItem->title ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $code->catalogItem->sku ?? '-' }}</div>
                                @else
                                    <span class="text-sm text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($code->assigned_email)
                                    <span class="text-sm text-slate-900">{{ $code->assigned_email }}</span>
                                @else
                                    <span class="text-sm text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                {{ $code->usage_count ?? 0 }}
                                @if ($code->usage_limit)
                                    / {{ $code->usage_limit }}
                                @else
                                    / ∞
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">
                                @php
                                    $startsAt  = $code->starts_at  ? \Illuminate\Support\Carbon::parse($code->starts_at)->format('d M Y')  : 'Any';
                                    $expiresAt = $code->expires_at ? \Illuminate\Support\Carbon::parse($code->expires_at)->format('d M Y') : 'Any';
                                @endphp
                                <span class="text-xs text-slate-500">{{ $startsAt }} → {{ $expiresAt }}</span>
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($code->disabled_at)
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Disabled</span>
                                @elseif ($code->is_active)
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="orders-empty">No discount codes match this search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Discount codes pagination',
                'baseQuery'   => array_filter([
                    'q' => $q ?: null,
                ]),
            ])
        @endif
    </section>
@endsection
