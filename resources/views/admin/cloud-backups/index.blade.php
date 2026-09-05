@extends('admin.layout')

@php
    $activeSection = 'cloud-backups';
    $state = $filters['state'] ?? 'all';
    $q = $filters['q'] ?? request('q', '');
    $formatBytes = fn ($bytes) => $bytes === null ? '—' : number_format((float) $bytes / 1048576, 2).' MB';
    $formatDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y, H:i') : '—';
    $thClass = 'border-b border-[#e2eaf0] px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.04em] text-[#647384] whitespace-nowrap';
    $tdClass = 'border-b border-[#edf2f6] px-4 py-3 text-sm text-[#334155] align-top';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e5edf3] bg-[#fbfdff] px-4 py-4" id="cloud-backups-filter-form">
            <div class="flex flex-wrap items-center gap-2">
                <input id="cloud-backups-search" name="q" type="search" value="{{ $q }}" placeholder="Search UID, resource, or user" class="h-9 min-w-[280px] rounded-md border border-[#d8e2ea] px-3 text-sm focus:ring-2 focus:ring-admin-primary/20" oninput="clearTimeout(window._cloudBackupsSearchTimer); window._cloudBackupsSearchTimer = setTimeout(() => document.getElementById('cloud-backups-filter-form').submit(), 300)">
                <select name="state" class="h-9 rounded-md border border-[#d8e2ea] px-2 text-sm font-semibold" onchange="document.getElementById('cloud-backups-filter-form').submit()">
                    <option value="all" @selected($state === 'all')>All states</option>
                    <option value="active" @selected($state === 'active')>Active</option>
                    <option value="deleted" @selected($state === 'deleted')>Deleted</option>
                </select>
            </div>
            <p class="text-sm font-semibold text-[#66717a]">{{ $pagination['total'] ? $pagination['from'].'–'.$pagination['to'].' of '.number_format($pagination['total']) : '0 results' }}</p>
        </form>

        <div class="overflow-x-auto"><table class="w-full min-w-[1100px] border-collapse">
            <thead><tr class="bg-[#eef3f7]"><th class="{{ $thClass }}">Cloud Backup</th><th class="{{ $thClass }}">User</th><th class="{{ $thClass }}">Resource ID</th><th class="{{ $thClass }}">Entities</th><th class="{{ $thClass }}">Size</th><th class="{{ $thClass }}">Status</th><th class="{{ $thClass }}">Updated</th><th class="{{ $thClass }} text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($data as $backup)
                    @php
                        $isDeleted = ! empty($backup->deleted_at);
                        $status = $isDeleted ? 'Deleted' : ($backup->status?->name ?? 'Unknown');
                        $statusClass = $isDeleted ? 'border-rose-200 bg-rose-50 text-rose-700' : ($backup->status?->name === 'ACTIVE' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700');
                    @endphp
                    <tr class="admin-interactive-row cursor-pointer" onclick="window.location='{{ route('admin.cloud-backups.show', $backup->uid) }}'">
                        <td class="{{ $tdClass }} font-mono text-xs"><a class="font-semibold text-[#2563eb] hover:underline" href="{{ route('admin.cloud-backups.show', $backup->uid) }}">{{ $backup->uid }}</a></td>
                        <td class="{{ $tdClass }}"><p class="font-semibold">{{ $backup->user?->name ?? '—' }}</p><p class="mt-1 text-xs text-[#64748b]">{{ $backup->user?->email ?? $backup->user_id }}</p></td>
                        <td class="{{ $tdClass }} font-mono text-xs">{{ $backup->resource_id ?? '—' }}</td>
                        <td class="{{ $tdClass }}">{{ number_format($backup->cloud_entities_count) }}</td>
                        <td class="{{ $tdClass }} font-semibold">{{ $formatBytes($backup->expected_size) }}</td>
                        <td class="{{ $tdClass }}"><span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $statusClass }}">{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</span></td>
                        <td class="{{ $tdClass }} whitespace-nowrap">{{ $formatDate($backup->updated_at) }}</td>
                        <td class="{{ $tdClass }} text-right" onclick="event.stopPropagation()">
                            @include('admin.partials.table-actions-dropdown', [
                                'buttonLabel' => 'Options',
                                'items' => [
                                    [
                                        'label' => 'View details',
                                        'url' => route('admin.cloud-backups.show', $backup->uid),
                                        'method' => 'GET',
                                        'color' => 'slate',
                                    ],
                                    [
                                        'label' => 'Queue verify job',
                                        'url' => route('admin.cloud-backups.verify', $backup->uid),
                                        'method' => 'POST',
                                        'color' => 'emerald',
                                        'disabled' => $isDeleted,
                                    ],
                                    [
                                        'label' => 'Delete cloud backup',
                                        'url' => route('admin.cloud-backups.destroy', $backup->uid),
                                        'method' => 'DELETE',
                                        'color' => 'rose',
                                        'disabled' => $isDeleted,
                                        'confirm' => 'Delete this cloud backup and all associated S3 objects? This cannot be undone.',
                                        'fields' => ['confirmDelete' => 'DELETE'],
                                    ],
                                ],
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>@include('admin.partials.table-empty', ['colspan' => 8, 'title' => 'No cloud backups found', 'message' => 'Try adjusting your filters or search.'])</tr>
                @endforelse
            </tbody>
        </table></div>
        @if (($pagination['last_page'] ?? 1) > 1)
            @include('partials.pagination', ['currentPage' => $pagination['current_page'], 'lastPage' => $pagination['last_page'], 'label' => 'Cloud backup pagination', 'baseQuery' => array_filter(['q' => $q ?: null, 'state' => $state !== 'all' ? $state : null])])
        @endif
    </section>
@endsection