@extends('admin.layout')

@php
    $activeSection = 'cloud-shares';

    $currentPage = $pagination['current_page'] ?? 1;
    $lastPage = $pagination['last_page'] ?? 1;
    $from = $pagination['from'] ?? 0;
    $to = $pagination['to'] ?? 0;
    $total = $pagination['total'] ?? 0;

    $state = $filters['state'] ?? 'all';
    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0] whitespace-nowrap';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';

    $formatDate = function ($value) {
        if (empty($value)) {
            return '—';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d M Y, H:i');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y, H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $formatBytes = function ($bytes) {
        if ($bytes === null || $bytes === '') {
            return '—';
        }

        $bytes = (float) $bytes;

        if ($bytes < 1024) {
            return number_format($bytes, 0).' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value = $value / 1024;
            $unitIndex++;
        }

        return number_format($value, 2).' '.$units[$unitIndex];
    };
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="cloud-shares-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[280px]"
                       for="cloud-shares-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="cloud-shares-search" name="q" type="search" placeholder="Search UID, resource, or user"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent focus:ring-2 focus:ring-admin-primary/20"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._cloudSharesSearchTimer); window._cloudSharesSearchTimer = setTimeout(() => document.getElementById('cloud-shares-filter-form').submit(), 300)">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="cloud-shares-state-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">State</span>
                    <select id="cloud-shares-state-filter" name="state"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold focus:ring-2 focus:ring-admin-primary/20"
                            onchange="document.getElementById('cloud-shares-filter-form').submit()">
                        <option value="all" @selected($state === 'all')>All</option>
                        <option value="active" @selected($state === 'active')>Active</option>
                        <option value="deleted" @selected($state === 'deleted')>Deleted</option>
                    </select>
                </label>
            </div>

            <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                @if ($total)
                    {{ $from }}&ndash;{{ $to }} of {{ number_format($total) }}
                @else
                    0 results
                @endif
            </p>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[1120px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Cloud Share</th>
                        <th class="{{ $thClass }}">User</th>
                        <th class="{{ $thClass }}">Resource ID</th>
                        <th class="{{ $thClass }}">Entities</th>
                        <th class="{{ $thClass }}">Verification</th>
                        <th class="{{ $thClass }}">Size</th>
                        <th class="{{ $thClass }}">State</th>
                        <th class="{{ $thClass }}">Updated</th>
                        <th class="{{ $thClass }} text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $share)
                        @php
                            $shareUid = $share->uid;
                            $isDeleted = ! empty($share->deleted_at);
                            $entityCount = (int) ($share->entities_count ?? 0);
                            $verifiedEntities = (int) ($share->verified_entities_count ?? 0);
                            $isFullyVerified = $entityCount > 0 && $verifiedEntities === $entityCount;
                            $stateClass = $isDeleted
                                ? 'border-rose-200 bg-rose-50 text-rose-700'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700';
                            $verificationClass = $isFullyVerified
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-amber-200 bg-amber-50 text-amber-700';
                        @endphp

                        <tr
                            id="cloud-share-row-{{ $shareUid }}"
                            class="admin-interactive-row scroll-mt-24 cursor-pointer"
                            onclick="window.location='{{ route('admin.cloud-shares.show', ['cloudShareUid' => $shareUid]) }}'"
                        >
                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <a class="font-semibold text-[#2563eb] hover:underline"
                                   href="{{ route('admin.cloud-shares.show', ['cloudShareUid' => $shareUid]) }}">
                                    {{ $shareUid }}
                                </a>
                            </td>

                            <td class="{{ $tdClass }}">
                                <div class="font-semibold text-[#1f2d39]">
                                    {{ $share->user?->name ?? '—' }}
                                </div>
                                <div class="mt-1 text-[0.76rem] text-slate-500">
                                    {{ $share->user?->email ?? $share->user_id }}
                                </div>
                            </td>

                            <td class="{{ $tdClass }}">
                                <span class="font-mono text-xs text-[#64748b]">{{ $share->resource_id ?? '—' }}</span>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                {{ number_format($entityCount) }}
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold {{ $verificationClass }}">
                                    {{ $isFullyVerified ? 'Verified' : 'Pending' }}
                                </span>
                                <div class="mt-1 text-[0.74rem] text-slate-500">
                                    {{ number_format($verifiedEntities) }}/{{ number_format($entityCount) }} entities
                                </div>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                {{ $formatBytes($share->expected_size) }}
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold {{ $stateClass }}">
                                    {{ $isDeleted ? 'Deleted' : 'Active' }}
                                </span>
                            </td>

                            <td class="{{ $tdClass }} whitespace-nowrap">
                                {{ $formatDate($share->updated_at) }}
                            </td>

                            <td class="{{ $tdClass }} text-right" onclick="event.stopPropagation()">
                                @include('admin.partials.table-actions-dropdown', [
                                    'buttonLabel' => 'Options',
                                    'items' => [
                                        [
                                            'label' => 'View details',
                                            'url' => route('admin.cloud-shares.show', ['cloudShareUid' => $shareUid]),
                                            'method' => 'GET',
                                            'color' => 'slate',
                                        ],
                                        [
                                            'label' => 'Queue verify job',
                                            'url' => route('admin.cloud-shares.verify', ['cloudShareUid' => $shareUid]),
                                            'method' => 'POST',
                                            'color' => 'emerald',
                                            'disabled' => $isDeleted,
                                        ],
                                        [
                                            'label' => 'Queue expire job',
                                            'url' => route('admin.cloud-shares.expire', ['cloudShareUid' => $shareUid]),
                                            'method' => 'POST',
                                            'color' => 'amber',
                                            'disabled' => $isDeleted,
                                        ],
                                        [
                                            'label' => 'Run manual cleanup',
                                            'url' => route('admin.cloud-shares.cleanup', ['cloudShareUid' => $shareUid]),
                                            'method' => 'POST',
                                            'color' => 'amber',
                                            'disabled' => $isDeleted,
                                            'confirm' => 'Run manual cleanup now? This can delete S3 files and archive the share.',
                                        ],
                                        [
                                            'label' => 'Delete cloud share',
                                            'url' => route('admin.cloud-shares.destroy', ['cloudShareUid' => $shareUid]),
                                            'method' => 'DELETE',
                                            'color' => 'rose',
                                            'disabled' => $isDeleted,
                                            'confirm' => 'Delete cloud share and all associated entities/S3 objects? This cannot be undone.',
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
                                'colspan' => 9,
                                'title' => 'No cloud shares found',
                                'message' => 'Try adjusting your filters or search.',
                            ])
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.pagination', [
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'label' => 'Cloud share pagination',
                'baseQuery' => array_filter([
                    'q' => $q ?: null,
                    'state' => $state !== 'all' ? $state : null,
                ]),
            ])
        @endif
    </section>
@endsection
