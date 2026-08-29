@extends('admin.layout')

@php
    $activeSection = 'stats';

    $overall = collect($stats)->firstWhere('key', 'overall') ?? [];
    $weekly = collect($stats)->firstWhere('key', 'weekly') ?? [];

    $overallItems = collect($overall['items'] ?? []);
    $weeklyItems = collect($weekly['items'] ?? []);

    $metric = function (\Illuminate\Support\Collection $items, string $label, int $default = 0): int {
        $value = $items->firstWhere('label', $label)['value'] ?? $default;

        return (int) $value;
    };

    $metricUrl = function (\Illuminate\Support\Collection $items, string $label): ?string {
        $url = $items->firstWhere('label', $label)['details_url'] ?? null;

        return is_string($url) && $url !== '' ? $url : null;
    };

    $totalUsers = $metric($overallItems, 'Total Users');
    $totalRegistrations = $metric($overallItems, 'Registers');
    $totalLogins = $metric($overallItems, 'Logins');
    $totalCloudShares = $metric($overallItems, 'Cloud Shares');
    $activeCloudShares = $metric($overallItems, 'Active Cloud Shares');
    $deletedCloudShares = $metric($overallItems, 'Deleted Cloud Shares');

    $weekUsers = $metric($weeklyItems, 'Total Users');
    $weekRegistrations = $metric($weeklyItems, 'Registers');
    $weekLogins = $metric($weeklyItems, 'Logins');
    $weekCloudShares = $metric($weeklyItems, 'Cloud Shares');

    $loginPerUser = $totalUsers > 0 ? round($totalLogins / $totalUsers, 2) : 0;
    $registrationRate = $totalUsers > 0 ? round(($totalRegistrations / $totalUsers) * 100, 1) : 0;
    $activeShareRate = $totalCloudShares > 0 ? round(($activeCloudShares / $totalCloudShares) * 100, 1) : 0;

    $activityMax = max(1, $weekLogins, $weekRegistrations);
    $activityBars = [
        max(8, (int) round(($weekRegistrations / $activityMax) * 48)),
        max(8, (int) round(($weekLogins / $activityMax) * 48)),
        max(8, (int) round((($weekRegistrations + $weekLogins) / max(1, $activityMax * 2)) * 48)),
        max(8, (int) round(($weekUsers / max(1, $activityMax)) * 32)),
    ];

    $usersDetailsUrl = $metricUrl($overallItems, 'Total Users') ?? $metricUrl($overallItems, 'Registers');
    $loginsDetailsUrl = $metricUrl($overallItems, 'Logins');
    $sharesDetailsUrl = $metricUrl($overallItems, 'Cloud Shares');

    $insights = collect();

    if ($weekLogins > 0) {
        $insights->push('This week recorded '.number_format($weekLogins).' logins across the platform.');
    } else {
        $insights->push('No login activity recorded this week.');
    }

    if ($weekRegistrations > 0) {
        $insights->push(number_format($weekRegistrations).' registrations were created this week.');
    } else {
        $insights->push('No new registrations were created this week.');
    }

    if ($totalCloudShares > 0) {
        $insights->push(number_format($activeCloudShares).' of '.number_format($totalCloudShares).' cloud shares are currently active.');
    } else {
        $insights->push('Cloud share activity is currently flat with no created shares.');
    }
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1280px] space-y-6">
        <header class="flex flex-col gap-4 rounded-[1.3rem] border border-zinc-400 bg-white/90 p-5 shadow-[0_10px_30px_rgba(15,23,42,0.05)] backdrop-blur-sm md:flex-row md:items-start md:justify-between">
            <div class="max-w-3xl">
                <h2 class="m-0 text-2xl font-bold tracking-tight text-admin-ink">Stats</h2>
                <p class="mt-2 text-sm leading-6 text-admin-muted">
                    Platform activity and performance overview, combining all-time totals with this-week momentum.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-xl border border-zinc-400 bg-[#fbfdff] px-3 py-2 text-[0.75rem] font-semibold text-[#5f6d79]">Snapshot: Overall + This week</span>
            </div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-zinc-400 bg-white px-4 py-3 shadow-[0_6px_20px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Total users</p>
                <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($totalUsers) }}</p>
                <p class="mt-1 text-[0.78rem] text-[#6f7c89]">{{ number_format($weekUsers) }} this week</p>
            </article>

            <article class="rounded-2xl border border-zinc-400 bg-white px-4 py-3 shadow-[0_6px_20px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Registrations</p>
                <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($totalRegistrations) }}</p>
                <p class="mt-1 text-[0.78rem] text-[#6f7c89]">{{ number_format($weekRegistrations) }} this week</p>
            </article>

            <article class="rounded-2xl border border-zinc-400 bg-white px-4 py-3 shadow-[0_6px_20px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Logins</p>
                <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($totalLogins) }}</p>
                <p class="mt-1 text-[0.78rem] text-[#6f7c89]">{{ number_format($weekLogins) }} this week</p>
            </article>

            <article class="rounded-2xl border border-zinc-400 bg-white px-4 py-3 shadow-[0_6px_20px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Cloud shares</p>
                <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($totalCloudShares) }}</p>
                <p class="mt-1 text-[0.78rem] text-[#6f7c89]">{{ number_format($activeCloudShares) }} active</p>
            </article>
        </section>

        <section class="rounded-[1.2rem] border border-zinc-400 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.04)]">
            <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Insights</p>

            <ul class="mt-3 space-y-2 p-0 text-sm text-[#2e3c49]">
                @foreach ($insights as $insight)
                    <li class="rounded-lg bg-[#f6fafb] px-3 py-2 list-none">{{ $insight }}</li>
                @endforeach
            </ul>
        </section>

        <section class="grid gap-4 xl:grid-cols-[2fr_1fr]">
            <article class="rounded-[1.3rem] border border-zinc-400 bg-white p-5 shadow-[0_10px_28px_rgba(15,23,42,0.05)]">
                <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Login activity</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-admin-ink">{{ number_format($totalLogins) }}</p>
                <p class="mt-1 text-[0.82rem] text-[#6f7c89]">Total logins recorded</p>

                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <div class="rounded-xl border border-zinc-400 bg-[#fbfdff] px-3 py-2">
                        <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">This week</p>
                        <p class="mt-1 text-lg font-bold text-admin-ink">{{ number_format($weekLogins) }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-400 bg-[#fbfdff] px-3 py-2">
                        <p class="m-0 text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Login / user ratio</p>
                        <p class="mt-1 text-lg font-bold text-admin-ink">{{ number_format($loginPerUser, 2) }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Activity index</p>
                    <div class="mt-3 flex items-end gap-2">
                        @foreach ($activityBars as $bar)
                            <span class="w-8 rounded-t-md bg-[#8dcfc8]" style="height: {{ $bar }}px"></span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-[0.76rem] text-[#6f7c89]">Weekly index based on registrations, logins, and active users.</p>
                </div>
            </article>

            <article class="rounded-[1.3rem] border border-zinc-400 bg-white p-5 shadow-[0_10px_28px_rgba(15,23,42,0.05)]">
                <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Platform snapshot</p>

                <dl class="mt-3 space-y-2">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Total users</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($totalUsers) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Active shares</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($activeCloudShares) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Cloud shares</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($totalCloudShares) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Deleted shares</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($deletedCloudShares) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Registration rate</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($registrationRate, 1) }}%</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-[#6f7c89]">Active share rate</dt>
                        <dd class="font-semibold text-admin-ink">{{ number_format($activeShareRate, 1) }}%</dd>
                    </div>
                </dl>

                @if ($usersDetailsUrl)
                    <a href="{{ $usersDetailsUrl }}" class="mt-4 inline-flex items-center text-sm font-semibold text-[#1f6f66] no-underline hover:text-[#16564f]">View user analytics →</a>
                @endif
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-[1.2rem] border border-zinc-400 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Registrations</p>
                <p class="mt-2 text-2xl font-bold text-admin-ink">{{ number_format($totalRegistrations) }} total</p>
                <p class="mt-1 text-[0.82rem] text-[#6f7c89]">{{ number_format($weekRegistrations) }} added in the current week window.</p>

                <div class="mt-3 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-[#8dcfc8]" style="width: {{ min(100, max(4, (int) round(($weekRegistrations / max(1, $totalRegistrations)) * 100))) }}%"></div>
                </div>

                @if ($usersDetailsUrl)
                    <a href="{{ $usersDetailsUrl }}" class="mt-3 inline-flex items-center text-sm font-semibold text-[#1f6f66] no-underline hover:text-[#16564f]">View registrations →</a>
                @endif
            </article>

            <article class="rounded-[1.2rem] border border-zinc-400 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.04)]">
                <p class="m-0 text-[0.74rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Cloud shares</p>
                <p class="mt-2 text-2xl font-bold text-admin-ink">{{ number_format($totalCloudShares) }} total</p>

                @if ($weekCloudShares > 0)
                    <p class="mt-1 text-[0.82rem] text-[#6f7c89]">{{ number_format($weekCloudShares) }} created in the current week window.</p>
                @else
                    <p class="mt-1 text-[0.82rem] text-[#6f7c89]">No new cloud shares in the current week window.</p>
                @endif

                <div class="mt-3 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-[#99b8d6]" style="width: {{ min(100, max(2, (int) round(($activeCloudShares / max(1, $totalCloudShares)) * 100))) }}%"></div>
                </div>

                @if ($sharesDetailsUrl)
                    <a href="{{ $sharesDetailsUrl }}" class="mt-3 inline-flex items-center text-sm font-semibold text-[#1f6f66] no-underline hover:text-[#16564f]">View cloud share activity →</a>
                @endif
            </article>
        </section>

    </div>
@endsection
