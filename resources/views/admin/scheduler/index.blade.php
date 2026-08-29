@extends('admin.layout')

@php
    $activeSection = 'scheduler';
    $jobs = collect($jobs ?? []);
    $jobsWithLogs = $jobs->whereNotNull('output')->count();

    $describeExpression = function (?string $expression): string {
        $value = trim((string) $expression);

        return match ($value) {
            '* * * * *' => 'Every minute',
            '*/2 * * * *' => 'Every 2 minutes',
            '*/4 * * * *' => 'Every 4 minutes',
            '*/5 * * * *' => 'Every 5 minutes',
            '*/10 * * * *' => 'Every 10 minutes',
            '*/15 * * * *' => 'Every 15 minutes',
            '0 * * * *' => 'Hourly',
            '0 */4 * * *' => 'Every 4 hours',
            '0 0 * * *' => 'Daily at midnight',
            default => 'Custom schedule',
        };
    };

    $statusData = function (array $job): array {
        $iso = $job['next_run_at_iso'] ?? null;

        if (! is_string($iso) || trim($iso) === '') {
            return ['label' => 'Unknown', 'tone' => 'slate'];
        }

        try {
            $nextRunAt = \Illuminate\Support\Carbon::parse($iso);
        } catch (\Throwable) {
            return ['label' => 'Unknown', 'tone' => 'slate'];
        }

        if ($nextRunAt->isPast() || $nextRunAt->equalTo(now())) {
            return ['label' => 'Due now', 'tone' => 'amber'];
        }

        return ['label' => 'Healthy', 'tone' => 'emerald'];
    };

    $statusToneClasses = [
        'emerald' => 'bg-emerald-500 text-emerald-700',
        'amber' => 'bg-amber-500 text-amber-700',
        'rose' => 'bg-rose-500 text-rose-700',
        'slate' => 'bg-slate-400 text-slate-700',
    ];
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1280px] space-y-6">
        <header class="flex flex-col gap-4 rounded-[1.2rem] bg-white/90 p-5 shadow-[0_10px_28px_rgba(15,23,42,0.06)] backdrop-blur-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div class="max-w-3xl">
                    <h2 class="m-0 text-2xl font-bold tracking-tight text-admin-ink">Scheduler</h2>
                    <p class="mt-2 text-sm leading-6 text-admin-muted">
                        Runs Laravel scheduled jobs every minute. Server cron: <span class="font-semibold text-admin-ink">* * * * *</span> and runner command <span class="font-semibold text-admin-ink">/usr/bin/php /xxx/public_html/artisan schedule:run</span>.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.scheduler.run') }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-primary whitespace-nowrap">
                        <span class="w-4 h-4">@icon('tower-server')</span>
                        Run scheduler now
                    </button>
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="pill slate">{{ number_format($jobs->count()) }} jobs</span>
                <span class="pill slate">{{ number_format($jobsWithLogs) }} logs</span>
                <span class="pill emerald">All systems visible</span>
            </div>
        </header>

        <section class="overflow-hidden rounded-[1.2rem] bg-white shadow-[0_12px_30px_rgba(15,23,42,0.06)]">
            <div class="border-b border-[#ecf1f5] px-5 py-4">
                <h3 class="m-0 text-[1.02rem] font-bold text-admin-ink">Scheduled jobs</h3>
                <p class="mt-1 text-[0.82rem] text-admin-muted">Click a row to expand technical details.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse">
                    <thead>
                        <tr class="bg-[#f5f8fb]">
                            <th class="px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">Job</th>
                            <th class="px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">Schedule</th>
                            <th class="px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">Next run</th>
                            <th class="px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">Queue</th>
                            <th class="px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">Status</th>
                            <th class="px-4 py-3 text-right text-[0.72rem] font-bold uppercase tracking-[0.06em] text-[#667788]">&nbsp;</th>
                        </tr>
                    </thead>

                    @forelse ($jobs as $job)
                            @php
                                $status = $statusData($job);
                                $statusTone = $status['tone'];
                                $statusClasses = $statusToneClasses[$statusTone] ?? $statusToneClasses['slate'];

                                $badges = collect([
                                    $job['background'] ? 'Background' : 'No background',
                                    $job['without_overlapping'] ? $job['without_overlapping'].'m overlap' : 'No overlap lock',
                                    $job['stop_when_empty'] ? 'Stop when empty' : 'Persistent worker',
                                    $job['timeout'] ? $job['timeout'].'s timeout' : null,
                                ])->filter()->values();

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

                            <tbody x-data="{ open: false }" class="group">
                                <tr class="cursor-pointer align-top border-b border-[#eef3f7] transition hover:bg-[#f9fcff]" @click="open = !open" :aria-expanded="String(open)">
                                    <td class="px-4 py-3 text-[0.86rem] text-[#2d3b47]">
                                        <div class="font-semibold text-admin-ink">{{ $job['label'] }}</div>
                                        <div class="mt-0.5 text-[0.74rem] text-[#72808f]">{{ $job['key'] }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-[0.84rem] text-[#2d3b47]">
                                        <code class="rounded-md bg-slate-100 px-2 py-1 text-[0.76rem] text-slate-800">{{ $job['expression'] }}</code>
                                        <div class="mt-1 text-[0.74rem] text-[#72808f]">{{ $describeExpression($job['expression']) }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-[0.84rem] text-[#2d3b47]">
                                        @if ($job['next_run_at'])
                                            <div class="space-y-1" data-scheduler-countdown="{{ $job['next_run_at_iso'] }}">
                                                <div class="font-semibold text-admin-ink">{{ $job['next_run_at_label'] }}</div>
                                                <div class="text-[0.74rem] text-[#72808f]" data-scheduler-countdown-text>Counting down...</div>
                                            </div>
                                        @else
                                            <span class="text-[0.8rem] text-[#72808f]">Unavailable</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-[0.84rem] text-[#2d3b47]">
                                        <div class="font-medium text-admin-ink">{{ $job['queue'] ?? '-' }}</div>
                                        <div class="mt-0.5 text-[0.74rem] text-[#72808f]">{{ $job['connection'] ?? 'default' }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-[0.84rem]">
                                        <div class="inline-flex items-center gap-2" data-scheduler-status data-scheduler-status-countdown="{{ $job['next_run_at_iso'] ?? '' }}">
                                            <span class="h-2.5 w-2.5 rounded-full {{ explode(' ', $statusClasses)[0] }}" data-scheduler-status-dot></span>
                                            <span class="text-[0.8rem] font-semibold {{ explode(' ', $statusClasses)[1] }}" data-scheduler-status-text>{{ $status['label'] }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                                        @include('admin.partials.table-actions-dropdown', [
                                            'items' => $actionItems,
                                            'buttonLabel' => '•••',
                                            'buttonClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-base font-bold text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-300',
                                            'panelClass' => 'admin-popover absolute right-0 z-10 mt-2 min-w-44 origin-top-right outline-none',
                                        ])
                                    </td>
                                </tr>

                                <tr x-cloak x-show="open" x-transition.opacity.duration.120ms>
                                    <td colspan="6" class="border-b border-[#eef3f7] bg-[#fbfdff] px-4 pb-4 pt-2">
                                        <div class="grid gap-3 lg:grid-cols-[1.7fr_1fr]">
                                            <div>
                                                <div class="mb-2 flex flex-wrap gap-2">
                                                    @foreach ($badges as $badge)
                                                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[0.73rem] font-semibold text-[#5c6b78]">{{ $badge }}</span>
                                                    @endforeach
                                                </div>

                                                <div class="rounded-lg border border-slate-200 bg-white p-3">
                                                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Runner command</p>
                                                    <p class="mt-2 break-all font-mono text-[0.76rem] text-[#1f2d38]">{{ $job['command'] }}</p>
                                                </div>
                                            </div>

                                            <div class="rounded-lg border border-slate-200 bg-white p-3 text-[0.78rem] text-[#5f6d79]">
                                                <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Runtime details</p>
                                                <div class="mt-2 space-y-1.5">
                                                    <div>Mode: <span class="font-semibold text-admin-ink">{{ ucfirst($job['mode']) }}</span></div>
                                                    <div>Sleep: <span class="font-semibold text-admin-ink">{{ $job['sleep'] ?? '-' }}</span></div>
                                                    <div>Timeout: <span class="font-semibold text-admin-ink">{{ $job['timeout'] ?? '-' }}</span></div>
                                                    <div>Tries: <span class="font-semibold text-admin-ink">{{ $job['tries'] ?? '-' }}</span></div>
                                                    <div>Output: <span class="font-semibold text-admin-ink">{{ $job['output'] ?? '-' }}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-[#66717a]">
                                    No scheduler jobs are configured in config/classer.php.
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
        </section>

        <script>
            (() => {
                const nodes = Array.from(document.querySelectorAll('[data-scheduler-countdown]'));
                const statusNodes = Array.from(document.querySelectorAll('[data-scheduler-status]'));

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

                    statusNodes.forEach((node) => {
                        const iso = node.dataset.schedulerStatusCountdown;
                        const textNode = node.querySelector('[data-scheduler-status-text]');
                        const dotNode = node.querySelector('[data-scheduler-status-dot]');

                        if (! iso || ! textNode || ! dotNode) {
                            return;
                        }

                        const targetTime = Date.parse(iso);

                        if (Number.isNaN(targetTime)) {
                            textNode.textContent = 'Unknown';
                            textNode.className = 'text-[0.8rem] font-semibold text-slate-700';
                            dotNode.className = 'h-2.5 w-2.5 rounded-full bg-slate-400';
                            return;
                        }

                        const distance = targetTime - now;

                        if (distance <= 0) {
                            textNode.textContent = 'Due now';
                            textNode.className = 'text-[0.8rem] font-semibold text-amber-700';
                            dotNode.className = 'h-2.5 w-2.5 rounded-full bg-amber-500';
                            return;
                        }

                        textNode.textContent = `Next in ${formatDuration(distance)}`;
                        textNode.className = 'text-[0.8rem] font-semibold text-emerald-700';
                        dotNode.className = 'h-2.5 w-2.5 rounded-full bg-emerald-500';
                    });
                };

                updateCountdowns();
                window.setInterval(updateCountdowns, 1000);
            })();
        </script>
    </div>
@endsection