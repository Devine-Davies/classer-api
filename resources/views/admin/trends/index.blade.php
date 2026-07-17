@extends('admin.layout')

@php
    $activeSection = 'trends';

    $series = collect($series ?? []);
    $domainOptions = collect($domainOptions ?? []);

    $activeDomain = $activeDomain ?? $filters['domain'] ?? request('domain', 'users');
    $interval = $filters['interval'] ?? request('interval', 'daily');
    $startDate = $filters['start_date'] ?? request('start_date', now()->subDays(30)->toDateString());
    $endDate = $filters['end_date'] ?? request('end_date', now()->toDateString());

    $palette = ['#0f766e', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6'];

    $allValues = $series
        ->flatMap(fn (array $entry) => collect($entry['points'] ?? [])->pluck('y'))
        ->map(fn ($value) => (int) $value)
        ->values();

    $maxValue = max(1, (int) ($allValues->max() ?? 0));
    $bucketCount = (int) (collect($series->first()['points'] ?? [])->count());

    $chartWidth = 1200;
    $chartHeight = 360;
    $paddingLeft = 64;
    $paddingRight = 24;
    $paddingTop = 22;
    $paddingBottom = 44;
    $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
    $plotHeight = $chartHeight - $paddingTop - $paddingBottom;

    $yTicks = 4;
    $gridValues = collect(range(0, $yTicks))->map(
        fn (int $step) => (int) round($maxValue - (($maxValue / $yTicks) * $step))
    );

    $firstPoints = collect($series->first()['points'] ?? []);
    $firstLabel = $firstPoints->first()['x'] ?? null;
    $middleLabel = $firstPoints->count() > 2
        ? ($firstPoints->values()[intdiv($firstPoints->count() - 1, 2)]['x'] ?? null)
        : null;
    $lastLabel = $firstPoints->last()['x'] ?? null;

    $formatBucket = function (?string $bucket) use ($interval): string {
        if (! $bucket) {
            return '-';
        }

        $date = \Illuminate\Support\Carbon::parse($bucket);

        return match ($interval) {
            'hourly' => $date->format('d M H:i'),
            'daily' => $date->format('d M Y'),
            'weekly' => $date->startOfWeek()->format('d M').' - '.$date->endOfWeek()->format('d M'),
            'monthly' => $date->format('M Y'),
            'yearly' => $date->format('Y'),
            default => $date->format('d M Y'),
        };
    };
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Trends</h2>
        <p class="mt-[0.35rem] text-admin-muted">Analyze trend metrics using server-side filters and PHP-rendered results.</p>
    </header>

    <section class="border border-admin-stroke bg-white">
        <div class="border-b border-[#e5edf3] bg-[#fbfdff] px-4 py-[0.9rem]">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($domainOptions as $option)
                    @php
                        $value = (string) ($option['value'] ?? 'users');
                        $label = (string) ($option['label'] ?? ucfirst($value));
                        $isActive = $value === $activeDomain;
                    @endphp

                    <a
                        href="{{ route('admin.trends', array_filter([
                            'domain' => $value,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'interval' => $interval,
                        ])) }}"
                        class="inline-flex items-center gap-2 rounded-[0.65rem] border px-[0.7rem] py-[0.5rem] text-[0.8rem] font-semibold transition {{ $isActive ? 'border-[#b8dfdc] bg-admin-primary-soft text-admin-primary' : 'border-[#d8e2ea] bg-white text-[#3b4a56] hover:border-[#bfcdda]' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <form method="GET" action="{{ route('admin.trends') }}"
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="trends-filter-form">
            <input type="hidden" name="domain" value="{{ $activeDomain }}">

            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="trends-start-date">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Start</span>
                    <input id="trends-start-date" name="start_date" type="date"
                           value="{{ $startDate }}"
                           class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                           onchange="document.getElementById('trends-filter-form').submit()">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="trends-end-date">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">End</span>
                    <input id="trends-end-date" name="end_date" type="date"
                           value="{{ $endDate }}"
                           class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                           onchange="document.getElementById('trends-filter-form').submit()">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="trends-interval">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Interval</span>
                    <select id="trends-interval" name="interval"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('trends-filter-form').submit()">
                        <option value="hourly" @selected($interval === 'hourly')>Hourly</option>
                        <option value="daily" @selected($interval === 'daily')>Daily</option>
                        <option value="weekly" @selected($interval === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($interval === 'monthly')>Monthly</option>
                        <option value="yearly" @selected($interval === 'yearly')>Yearly</option>
                    </select>
                </label>
            </div>

            <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                @if ($bucketCount)
                    {{ number_format($bucketCount) }} buckets
                @else
                    0 results
                @endif
            </p>
        </form>

        <div class="grid gap-3 border-b border-[#e5edf3] bg-[#fbfdff] px-4 py-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($series as $entry)
                <article class="rounded-xl border border-[#dce6ee] bg-white px-4 py-3">
                    <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.05em] text-[#6f7c89]">{{ $entry['label'] ?? '-' }}</p>
                    <p class="mt-1 text-[1.15rem] font-bold text-[#24323e]">{{ number_format((int) ($entry['total'] ?? 0)) }}</p>
                </article>
            @endforeach
        </div>

        <div class="border-t border-[#e5edf3] px-4 py-4">
            @if ($series->isNotEmpty() && $bucketCount > 0)
                <div class="overflow-x-auto rounded-xl border border-[#dce6ee] bg-white p-3">
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-[24rem] min-w-[880px] w-full" role="img" aria-label="Trends line chart">
                        @foreach ($gridValues as $tick)
                            @php
                                $y = $paddingTop + ($plotHeight * (1 - ($tick / max(1, $maxValue))));
                            @endphp
                            <line x1="{{ $paddingLeft }}" y1="{{ $y }}" x2="{{ $chartWidth - $paddingRight }}" y2="{{ $y }}" stroke="#e5edf3" stroke-width="1" />
                            <text x="{{ $paddingLeft - 12 }}" y="{{ $y + 4 }}" text-anchor="end" font-size="11" fill="#7b8794">{{ number_format($tick) }}</text>
                        @endforeach

                        @foreach ($series as $index => $entry)
                            @php
                                $color = $palette[$index % count($palette)];
                                $points = collect($entry['points'] ?? [])->values();
                                $count = $points->count();
                                $stepX = $count > 1 ? ($plotWidth / ($count - 1)) : 0;

                                $pathPoints = $points->map(function ($point, $pointIndex) use ($paddingLeft, $stepX, $paddingTop, $plotHeight, $maxValue) {
                                    $x = $paddingLeft + ($stepX * $pointIndex);
                                    $value = (int) ($point['y'] ?? 0);
                                    $y = $paddingTop + ($plotHeight * (1 - ($value / max(1, $maxValue))));

                                    return number_format($x, 2, '.', '').','.number_format($y, 2, '.', '');
                                })->implode(' ');
                            @endphp

                            @if ($pathPoints !== '')
                                <polyline fill="none" stroke="{{ $color }}" stroke-width="2.5" points="{{ $pathPoints }}" />
                            @endif
                        @endforeach
                    </svg>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 text-[0.78rem] text-[#5f6d79]">
                        @foreach ($series as $index => $entry)
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $palette[$index % count($palette)] }}"></span>
                                {{ $entry['label'] ?? '-' }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4 text-[0.76rem] text-[#6f7c89]">
                        <span>{{ $formatBucket($firstLabel) }}</span>
                        @if ($middleLabel)
                            <span>{{ $formatBucket($middleLabel) }}</span>
                        @endif
                        <span>{{ $formatBucket($lastLabel) }}</span>
                    </div>
                </div>
            @else
                <div class="orders-empty">No trend data found for this filter.</div>
            @endif
        </div>
    </section>
@endsection
