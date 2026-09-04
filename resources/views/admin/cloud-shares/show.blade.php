@extends('admin.layout')

@php
    $activeSection = 'cloud-shares';

    $entities = collect($cloudShare->cloudEntities ?? []);
    $isDeleted = ! empty($cloudShare->deleted_at);

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

    $statusClass = $isDeleted
        ? 'border-rose-200 bg-rose-50 text-rose-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700';

    $entityTotalSize = (int) $entities->sum('size');
    $verifiedEntities = (int) $entities->filter(fn ($entity) => filled($entity->e_tag ?? null))->count();
    $isFullyVerified = $entities->count() > 0 && $verifiedEntities === $entities->count();
    $verificationClass = $isFullyVerified
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';
@endphp

@section('content')
    <div class="max-w-[1200px] space-y-6">
        <section class="rounded-[0.85rem] border border-[#dce6ef] bg-white shadow-sm overflow-hidden">
            <div class="px-6 pt-7 pb-6 border-b border-[#edf2f6]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="m-0 text-[1.4rem] font-bold text-[#020617]">Cloud Share: {{ $cloudShare->uid }}</h1>
                        <p class="mt-2 text-sm text-[#64748b]">Operational detail and entity diagnostics for this cloud share.</p>
                    </div>

                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                        {{ $isDeleted ? 'Deleted' : 'Active' }}
                    </span>
                </div>
            </div>

            <div class="px-5 py-5 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="text-[0.78rem] uppercase tracking-[0.06em] text-[#64748b] font-bold">User</div>
                    <div class="mt-1 text-sm font-semibold text-[#0f172a]">{{ $cloudShare->user?->email ?? $cloudShare->user_id }}</div>
                    <div class="mt-1 text-xs text-[#64748b]">{{ $cloudShare->user?->name ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-[0.78rem] uppercase tracking-[0.06em] text-[#64748b] font-bold">Entities</div>
                    <div class="mt-1 text-sm font-semibold text-[#0f172a]">{{ number_format($entities->count()) }}</div>
                    <div class="mt-1 text-xs text-[#64748b]">{{ $formatBytes($entityTotalSize) }} combined</div>
                </div>

                <div>
                    <div class="text-[0.78rem] uppercase tracking-[0.06em] text-[#64748b] font-bold">Verification</div>
                    <div class="mt-1">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold {{ $verificationClass }}">
                            {{ $isFullyVerified ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                    <div class="mt-1 text-xs text-[#64748b]">{{ number_format($verifiedEntities) }}/{{ number_format($entities->count()) }} entities with ETag</div>
                </div>

                <div>
                    <div class="text-[0.78rem] uppercase tracking-[0.06em] text-[#64748b] font-bold">Resource ID</div>
                    <div class="mt-1 text-xs font-mono text-[#334155] break-all">{{ $cloudShare->resource_id ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-[0.78rem] uppercase tracking-[0.06em] text-[#64748b] font-bold">Cloud Share Size</div>
                    <div class="mt-1 text-sm font-semibold text-[#0f172a]">{{ $formatBytes($cloudShare->expected_size) }}</div>
                </div>
            </div>

            <div class="border-t border-[#edf2f6] bg-[#f8fafc] px-5 py-4 text-xs text-[#64748b]">
                Created {{ $formatDate($cloudShare->created_at) }} · Updated {{ $formatDate($cloudShare->updated_at) }} · Deleted {{ $formatDate($cloudShare->deleted_at) }}
            </div>
        </section>

        <section class="rounded-[0.85rem] border border-[#dce6ef] bg-white shadow-sm overflow-hidden">
            <div class="px-6 pt-7 pb-6 border-b border-[#edf2f6]">
                <h2 class="m-0 text-base font-bold text-[#020617]">Operational Actions</h2>
                <p class="mt-2 text-sm text-[#64748b]">Run cloud-share jobs on demand. Duplicate accidental requests are guarded for a short period.</p>
            </div>

            <div class="px-5 py-5 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.cloud-shares.verify-now', ['cloudShareUid' => $cloudShare->uid]) }}" onsubmit="return confirm(@js('Verify this cloud share now? The page will wait for the S3 checks to finish.'))">
                    @csrf
                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        @disabled($isDeleted)
                    >
                        Verify Now
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.cloud-shares.verify', ['cloudShareUid' => $cloudShare->uid]) }}">
                    @csrf
                    <button
                        type="submit"
                        class="admin-btn admin-btn-success-soft"
                        @disabled($isDeleted)
                    >
                        Queue Verify Job
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.cloud-shares.expire', ['cloudShareUid' => $cloudShare->uid]) }}" onsubmit="return confirm(@js('Queue expire job now? This may delete S3 files for this share.'))">
                    @csrf
                    <button
                        type="submit"
                        class="admin-btn admin-btn-warn-soft"
                        @disabled($isDeleted)
                    >
                        Queue Expire Job
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.cloud-shares.cleanup', ['cloudShareUid' => $cloudShare->uid]) }}" onsubmit="return confirm(@js('Run manual cleanup command now? This removes S3 objects and finalizes cleanup.'))">
                    @csrf
                    <button
                        type="submit"
                        class="admin-btn admin-btn-neutral"
                        @disabled($isDeleted)
                    >
                        Run Manual Cleanup Command
                    </button>
                </form>
            </div>
        </section>

        <section class="rounded-[0.85rem] border border-[#dce6ef] bg-white shadow-sm overflow-hidden">
            <div class="px-5 pt-5 pb-4 border-b border-[#edf2f6]">
                <h2 class="m-0 text-base font-bold text-[#020617]">Cloud Share Entities</h2>
                <p class="mt-2 text-sm text-[#64748b]">Entity records and S3-related fields used for diagnostics.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1200px] border-collapse">
                    <thead>
                        <tr class="bg-[#f8fafc]">
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">UID</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Type</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Verification</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">ETag</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Size</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">S3 Key / Path</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Expires</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Created</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap">Deleted</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($entities as $entity)
                            @php
                                $entityVerified = filled($entity->e_tag ?? null);
                            @endphp
                            <tr>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">
                                    <span class="font-mono text-xs text-[#64748b]">{{ $entity->uid ?? '—' }}</span>
                                </td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">{{ $entity->mime_type ?? '—' }}</td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.74rem] font-bold {{ $entityVerified ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                        {{ $entityVerified ? 'Verified' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">
                                    <div class="font-mono text-xs text-[#64748b] break-all">{{ $entity->e_tag ?? '—' }}</div>
                                </td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">{{ $formatBytes($entity->expected_size ?? null) }}</td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">
                                    <div class="font-mono text-xs text-[#64748b] break-all">{{ $entity->key ?? '—' }}</div>
                                </td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">{{ $formatDate($entity->validated_at ?? null) }}</td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">{{ $formatDate($entity->created_at ?? null) }}</td>
                                <td class="py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top">{{ $formatDate($entity->deleted_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10 text-center text-sm text-[#64748b]" colspan="9">
                                    No cloud entities found for this share.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            @include('admin.partials.confirm-delete-form', [
                'action' => route('admin.cloud-shares.destroy', ['cloudShareUid' => $cloudShare->uid]),
                'method' => 'DELETE',
                'title' => 'Delete cloud share',
                'description' => 'This will permanently delete this cloud share record, all related cloud entities, and attempt to remove related S3 objects. This action cannot be undone.',
                'buttonLabel' => 'Delete cloud share',
                'confirmValue' => 'DELETE',
                'confirmLabel' => 'Type DELETE to confirm',
                'modalTitle' => 'Confirm cloud share deletion',
            ])
        </section>
    </div>
@endsection
