@extends('admin.layout')

@php
    $activeSection = 'stats';
    $dotClass = ['bg-green-500', 'bg-blue-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500'];
    $groupCount = collect($stats)->count();
    $metricCount = collect($stats)->sum(fn (array $group): int => count($group['items'] ?? []));
@endphp

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 rounded-[1.35rem] border border-admin-stroke bg-white/90 p-5 shadow-[0_10px_30px_rgba(15,23,42,0.05)] backdrop-blur-sm md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <span class="pill slate mb-3">Admin overview</span>
                <h2 class="m-0 text-2xl font-bold tracking-tight text-admin-ink">Stats</h2>
                <p class="mt-2 text-sm leading-6 text-admin-muted">
                    A compact snapshot of platform activity with direct links into the metrics that have drill-down pages.
                </p>
            </div>

            <div class="grid min-w-[18rem] grid-cols-2 gap-3">
                <div class="rounded-2xl border border-[#dce6ee] bg-[#fbfdff] px-4 py-3">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Groups</p>
                    <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($groupCount) }}</p>
                </div>
                <div class="rounded-2xl border border-[#dce6ee] bg-[#fbfdff] px-4 py-3">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Metrics</p>
                    <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($metricCount) }}</p>
                </div>
            </div>
        </header>

        @foreach ($stats as $group)
            <section class="overflow-hidden rounded-[1.35rem] border border-admin-stroke bg-white shadow-[0_12px_34px_rgba(15,23,42,0.05)]">
                <header class="flex flex-col gap-3 border-b border-[#e5edf3] bg-[#fbfdff] px-5 py-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h3 class="m-0 text-[1.05rem] font-bold text-admin-ink">{{ $group['label'] }}</h3>
                        <p class="mt-1 text-[0.84rem] leading-6 text-admin-muted">{{ $group['description'] }}</p>
                    </div>

                    <span class="pill slate self-start md:self-auto">
                        {{ number_format(count($group['items'] ?? [])) }} metrics
                    </span>
                </header>

                <div id="stats-container-{{ $group['key'] }}" class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($group['items'] as $stat)
                        @if (! empty($stat['details_url']))
                            <a href="{{ $stat['details_url'] }}" class="group flex items-start gap-4 rounded-2xl border border-[#dce6ee] bg-[#fbfdff] p-4 no-underline transition duration-150 hover:-translate-y-0.5 hover:border-[#b8dfdc] hover:bg-[#f7fcfb] hover:shadow-[0_8px_24px_rgba(15,23,42,0.06)]">
                                <div class="mt-1 h-3 w-3 shrink-0 rounded-full {{ $dotClass[$loop->index % count($dotClass)] }}"></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="m-0 text-[0.75rem] font-bold uppercase tracking-[0.08em] text-[#66717a]">{{ $stat['label'] }}</p>
                                        <span class="pill emerald shrink-0 opacity-90 transition group-hover:opacity-100">Details</span>
                                    </div>

                                    <h4 class="mt-2 text-[1.55rem] font-bold leading-none tracking-tight text-[#162127]">{{ $stat['formatted'] }}</h4>
                                    <p class="mt-2 text-[0.8rem] leading-5 text-[#6f7c89]">Open the detailed chart and filter view for this metric.</p>
                                </div>
                            </a>
                        @else
                            <article class="flex items-start gap-4 rounded-2xl border border-[#dce6ee] bg-[#fbfdff] p-4 transition duration-150 hover:-translate-y-0.5 hover:border-[#d1dde7] hover:shadow-[0_8px_24px_rgba(15,23,42,0.04)]">
                                <div class="mt-1 h-3 w-3 shrink-0 rounded-full {{ $dotClass[$loop->index % count($dotClass)] }}"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="m-0 text-[0.75rem] font-bold uppercase tracking-[0.08em] text-[#66717a]">{{ $stat['label'] }}</p>
                                    <h4 class="mt-2 text-[1.55rem] font-bold leading-none tracking-tight text-[#162127]">{{ $stat['formatted'] }}</h4>
                                    <p class="mt-2 text-[0.8rem] leading-5 text-[#6f7c89]">Currently included in this snapshot only.</p>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
