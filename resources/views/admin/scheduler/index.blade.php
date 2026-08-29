@extends('admin.layout')

@php
    $activeSection = 'scheduler';
    $jobs = collect($jobs ?? []);
@endphp

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 rounded-[1.35rem] border border-admin-stroke bg-white/90 p-5 shadow-[0_10px_30px_rgba(15,23,42,0.05)] backdrop-blur-sm md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <span class="pill slate mb-3">System scheduler</span>
                <h2 class="m-0 text-2xl font-bold tracking-tight text-admin-ink">Scheduler</h2>
                <p class="mt-2 leading-6">
                    <span class="font-semibold text-admin-ink">* * * * *</span> tells Laravel to check every minute, and <span class="font-semibold text-admin-ink">/usr/bin/php /xxx/public_html/artisan schedule:run</span> is the server cron command that starts it.
                </p>

                <form method="POST" action="{{ route('admin.scheduler.run') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <span class="w-4 h-4">@icon('tower-server')</span>
                        Run scheduler now
                    </button>
                </form>
            </div>

            <div class="grid min-w-[18rem] grid-cols-2 gap-3">
                <div class="rounded-2xl border border-[#dce6ee] bg-[#fbfdff] px-4 py-3">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Jobs</p>
                    <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($jobs->count()) }}</p>
                </div>
                <div class="rounded-2xl border border-[#dce6ee] bg-[#fbfdff] px-4 py-3">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Logs</p>
                    <p class="mt-1 text-2xl font-bold text-admin-ink">{{ number_format($jobs->whereNotNull('output')->count()) }}</p>
                </div>
            </div>
        </header>

        <section class="overflow-hidden rounded-[1.35rem] border border-admin-stroke bg-white shadow-[0_12px_34px_rgba(15,23,42,0.05)]">
            <div class="flex flex-col gap-3 border-b border-[#e5edf3] bg-[#fbfdff] px-5 py-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="m-0 text-[1.05rem] font-bold text-admin-ink">Scheduler config entries</h3>
                    <p class="mt-1 text-[0.84rem] leading-6 text-admin-muted">
                        Each row is loaded from the scheduler array in config/classer.php.
                    </p>
                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] border-collapse">
                    <thead>
                        <tr class="bg-[#eef3f7]">
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Job</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Mode</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Expression</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Next run</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Runner</th>
                            <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Settings</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($jobs as $job)
                            <tr class="admin-interactive-row align-top">
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    <div class="font-bold text-admin-ink">{{ $job['label'] }}</div>
                                    <div class="mt-1 text-[0.76rem] text-[#6f7c89]">{{ $job['key'] }}</div>
                                </td>
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    <span class="pill {{ $job['mode'] === 'artisan' ? 'emerald' : 'slate' }}">
                                        {{ ucfirst($job['mode']) }}
                                    </span>
                                </td>
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    <code class="rounded-lg bg-slate-100 px-2 py-1 text-[0.8rem] text-slate-800">{{ $job['expression'] }}</code>
                                </td>
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    @if ($job['next_run_at'])
                                        <div class="space-y-1" data-scheduler-countdown="{{ $job['next_run_at_iso'] }}">
                                            <div class="font-semibold text-admin-ink">{{ $job['next_run_at_label'] }}</div>
                                            <div class="text-[0.75rem] text-[#6f7c89]" data-scheduler-countdown-text>Counting down...</div>
                                        </div>
                                    @else
                                        <span class="text-[0.8rem] text-[#6f7c89]">Unavailable</span>
                                    @endif
                                </td>
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    <div class="font-mono text-[0.8rem] text-[#1f2d38]">{{ $job['command'] }}</div>
                                    @if ($job['connection'] || $job['queue'])
                                        <div class="mt-1 text-[0.75rem] text-[#6f7c89]">
                                            {{ $job['connection'] ? 'Connection: '.$job['connection'] : '' }}
                                            {{ $job['connection'] && $job['queue'] ? ' · ' : '' }}
                                            {{ $job['queue'] ? 'Queue: '.$job['queue'] : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">
                                    <div class="space-y-2 text-[0.78rem] text-[#5f6d79]">
                                        <div>Background: {{ $job['background'] ? 'Yes' : 'No' }}</div>
                                        <div>Overlap: {{ $job['without_overlapping'] ? $job['without_overlapping'].' min' : 'None' }}</div>
                                        <div>Stop when empty: {{ $job['stop_when_empty'] ? 'Yes' : 'No' }}</div>
                                        <div>Sleep: {{ $job['sleep'] ?? '-' }}</div>
                                        <div>Timeout: {{ $job['timeout'] ?? '-' }}</div>

                                        @php
                                            $actionItems = [];

                                            if ($job['output']) {
                                                $actionItems[] = [
                                                    'label' => 'View logs',
                                                    'url' => route('admin.logs', ['file' => $job['output']]),
                                                    'color' => 'slate',
                                                ];
                                            }

                                            $actionItems[] = [
                                                'label' => 'Run now',
                                                'url' => route('admin.scheduler.jobs.run', ['job' => $job['key']]),
                                                'method' => 'POST',
                                                'color' => 'emerald',
                                            ];
                                        @endphp

                                        @include('admin.partials.table-actions-dropdown', [
                                            'items' => $actionItems,
                                            'buttonLabel' => 'Options',
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-[0.9rem] py-10 text-center text-sm text-[#66717a]">
                                    No scheduler jobs are configured in config/classer.php.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <script>
            (() => {
                const nodes = Array.from(document.querySelectorAll('[data-scheduler-countdown]'));

                if (! nodes.length) {
                    return;
                }

                const formatDuration = (milliseconds) => {
                    const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    const parts = [];

                    if (days > 0) {
                        parts.push(`${days}d`);
                    }

                    if (hours > 0 || parts.length > 0) {
                        parts.push(`${hours}h`);
                    }

                    if (minutes > 0 || parts.length > 0) {
                        parts.push(`${minutes}m`);
                    }

                    parts.push(`${seconds}s`);

                    return parts.join(' ');
                };

                const updateCountdowns = () => {
                    const now = Date.now();

                    nodes.forEach((node) => {
                        const targetText = node.dataset.schedulerCountdown;
                        const textNode = node.querySelector('[data-scheduler-countdown-text]');

                        if (! textNode || ! targetText) {
                            return;
                        }

                        const targetTime = Date.parse(targetText);

                        if (Number.isNaN(targetTime)) {
                            textNode.textContent = 'Unable to calculate countdown.';
                            return;
                        }

                        const distance = targetTime - now;

                        if (distance <= 0) {
                            textNode.textContent = 'Due now';
                            return;
                        }

                        textNode.textContent = `In ${formatDuration(distance)}`;
                    });
                };

                updateCountdowns();
                window.setInterval(updateCountdowns, 1000);
            })();
        </script>
    </div>
@endsection