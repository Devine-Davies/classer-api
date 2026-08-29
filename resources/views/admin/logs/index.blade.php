@extends('admin.layout')

@php
    $activeSection = 'logs';
    $logs = collect($logs ?? []);
    $activeLogFile = $activeLogFile ?? $logs->first()['filename'] ?? null;
    $activeLogEntry = $logs->firstWhere('filename', $activeLogFile);
    $rows = collect($rows ?? []);

    $currentPage = $pagination['current_page'] ?? 1;
    $lastPage = $pagination['last_page'] ?? 1;
    $from = $pagination['from'] ?? 0;
    $to = $pagination['to'] ?? 0;
    $total = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');
    $limit = (int) ($filters['limit'] ?? request('limit', 50));

    $primaryFilenames = collect(['laravel.log', 'app.log', 'scheduler-mail.log']);
    $primaryLogs = $primaryFilenames
        ->map(fn (string $filename) => $logs->firstWhere('filename', $filename))
        ->filter();
    $otherLogs = $logs->filter(
        fn ($log) => ! $primaryFilenames->contains($log['filename'] ?? '')
    )->values();
    $allLogs = $primaryLogs->merge($otherLogs);

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
    $timeThClass = $thClass.' w-[12rem] whitespace-nowrap';
    $timeTdClass = $tdClass.' w-[12rem] whitespace-nowrap text-[0.82rem] leading-5';

    $levelClasses = [
        'EMERGENCY' => 'border-rose-200 bg-rose-50 text-rose-700',
        'ALERT' => 'border-rose-200 bg-rose-50 text-rose-700',
        'CRITICAL' => 'border-rose-200 bg-rose-50 text-rose-700',
        'ERROR' => 'border-rose-200 bg-rose-50 text-rose-700',
        'WARNING' => 'border-amber-200 bg-amber-50 text-amber-700',
        'NOTICE' => 'border-sky-200 bg-sky-50 text-sky-700',
        'INFO' => 'border-sky-200 bg-sky-50 text-sky-700',
        'DEBUG' => 'border-slate-200 bg-slate-50 text-slate-700',
        'TRACE' => 'border-violet-200 bg-violet-50 text-violet-700',
        'LOG' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];

    $activeLogSize = (int) ($activeLogEntry['size'] ?? 0);
    $activeLogSizeLabel = $activeLogSize >= 1048576
        ? number_format($activeLogSize / 1048576, 2).' MB'
        : number_format(max($activeLogSize, 0) / 1024, 1).' KB';
    $activeLogUpdatedAt = (int) ($activeLogEntry['last_modified'] ?? 0);
    $activeLogUpdatedLabel = $activeLogUpdatedAt > 0 ? date('M j, Y g:i A', $activeLogUpdatedAt) : null;
@endphp

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 rounded-[1.35rem] border border-admin-stroke bg-white/90 p-5 shadow-[0_10px_30px_rgba(15,23,42,0.05)] backdrop-blur-sm md:flex-row md:items-start md:justify-between">
            <div class="max-w-3xl">
                <span class="pill slate mb-3">System logs</span>
                <h2 class="m-0 text-2xl font-bold tracking-tight text-admin-ink">Application Logs</h2>
                <p class="mt-2 text-sm leading-6 text-admin-muted">
                    Inspect Laravel and scheduler output from a single screen.
                </p>
            </div>
        </header>

        @if ($allLogs->isNotEmpty())
            <section class="rounded-[1.35rem] border border-admin-stroke bg-white p-4 shadow-[0_12px_34px_rgba(15,23,42,0.05)]">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($allLogs as $log)
                        @php
                            $filename = $log['filename'] ?? null;
                            $isActive = $filename !== null && $filename === $activeLogFile;
                            $size = (int) ($log['size'] ?? 0);
                            $sizeLabel = $size >= 1048576
                                ? number_format($size / 1048576, 2).' MB'
                                : number_format(max($size, 0) / 1024, 1).' KB';
                        @endphp

                        @if ($filename)
                            <a
                                href="{{ route('admin.logs', array_filter(['file' => $filename, 'q' => $q !== '' ? $q : null, 'limit' => $limit !== 50 ? $limit : null])) }}"
                                class="admin-btn admin-btn-neutral admin-btn-sm {{ $isActive ? 'border-[#8dcfc8] bg-[#eaf8f6]' : '' }}"
                            >
                                <span>{{ $filename }}</span>
                                <span>{{ $sizeLabel }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-[1.35rem] border border-admin-stroke bg-white shadow-[0_12px_34px_rgba(15,23,42,0.05)]">
            <div class="border-b border-[#e5edf3] px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="m-0 text-[0.72rem] font-bold uppercase tracking-[0.08em] text-[#6f7c89]">Viewing</p>

                    @if ($activeLogFile)
                        @include('admin.partials.table-actions-dropdown', [
                            'buttonLabel' => 'Options',
                            'items' => [
                                [
                                    'label' => 'Back up logs',
                                    'url' => route('admin.logs.backup'),
                                    'method' => 'POST',
                                    'color' => 'emerald',
                                    'confirm' => 'Back up this log file?',
                                    'fields' => [
                                        'file' => $activeLogFile,
                                        'q' => $q,
                                        'limit' => $limit,
                                    ],
                                ],
                                [
                                    'label' => 'Clear log file',
                                    'url' => route('admin.logs.clear'),
                                    'method' => 'POST',
                                    'color' => 'rose',
                                    'confirm' => 'Clear this log file? This cannot be undone.',
                                    'fields' => [
                                        'file' => $activeLogFile,
                                        'confirm_file' => $activeLogFile,
                                        'q' => $q,
                                        'limit' => $limit,
                                    ],
                                ],
                            ],
                        ])
                    @endif
                </div>

                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <h3 class="m-0 text-[1.02rem] font-bold text-admin-ink">{{ $activeLogFile ?? 'No log file selected' }}</h3>
                    @if ($activeLogEntry)
                        <span class="text-[0.82rem] text-[#6f7c89]">{{ $activeLogSizeLabel }} · {{ $activeLogUpdatedLabel ?? 'Unknown updated time' }}</span>
                    @endif
                </div>
            </div>

            <div class="border-b border-[#e5edf3] px-4 py-4">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <form method="GET" action="{{ route('admin.logs') }}" id="logs-filter-form" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:flex-wrap">
                        @if ($activeLogFile)
                            <input type="hidden" name="file" value="{{ $activeLogFile }}">
                        @endif

                        <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.8rem] bg-[#fbfdff] h-[2.5rem] px-[0.7rem] min-w-[260px] shadow-sm"
                               for="logs-search">
                            <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                            <input id="logs-search" name="q" type="search" placeholder="Search lines"
                                   class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent focus:ring-2 focus:ring-admin-primary/20"
                                   value="{{ $q }}"
                                   oninput="clearTimeout(window._logsSearchTimer); window._logsSearchTimer = setTimeout(() => document.getElementById('logs-filter-form').submit(), 300)">
                        </label>

                        <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.8rem] bg-[#fbfdff] h-[2.5rem] px-[0.7rem] shadow-sm"
                               for="logs-limit-filter">
                            <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Rows</span>
                            <select id="logs-limit-filter" name="limit"
                                    class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold focus:ring-2 focus:ring-admin-primary/20"
                                    onchange="document.getElementById('logs-filter-form').submit()">
                                <option value="25" @selected($limit === 25)>25</option>
                                <option value="50" @selected($limit === 50)>50</option>
                                <option value="100" @selected($limit === 100)>100</option>
                                <option value="200" @selected($limit === 200)>200</option>
                            </select>
                        </label>
                    </form>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2 text-sm font-semibold text-[#66717a]">
                            <span class="pill slate">{{ $total ? number_format($from).'–'.number_format($to) : 'No rows' }}</span>
                            <span>of {{ number_format($total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[860px]">
                    <thead>
                        <tr class="bg-[#eef3f7]">
                            <th class="{{ $thClass }}">Level</th>
                            <th class="{{ $timeThClass }}">Time</th>
                            <th class="{{ $thClass }}">Context</th>
                            <th class="{{ $thClass }}">Message</th>
                        </tr>
                    </thead>

                    @forelse ($rows as $row)
                        @php
                            $level = strtoupper((string) ($row->type ?? 'LOG'));
                            $pillClass = $levelClasses[$level] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                        @endphp
                        <tbody x-data="{ open: false }" class="group">
                            <tr class="cursor-pointer hover:bg-slate-50" @click="open = !open" :aria-expanded="String(open)">
                                <td class="{{ $tdClass }}">
                                    <span class="pill {{ $pillClass }}">{{ $level }}</span>
                                </td>
                                <td class="{{ $timeTdClass }} text-admin-muted">{{ $row->timestamp ?? '-' }}</td>
                                <td class="{{ $tdClass }} text-admin-muted">{{ $row->context ?? 'raw' }}</td>
                                <td class="{{ $tdClass }} text-admin-ink">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="truncate">{{ $row->message ?? '-' }}</span>
                                        <span class="shrink-0 text-[0.72rem] font-semibold uppercase tracking-[0.04em] text-[#8391a0]" x-text="open ? 'Hide' : 'View'"></span>
                                    </div>
                                </td>
                            </tr>
                            <tr x-cloak x-show="open" x-transition.opacity.duration.120ms>
                                <td colspan="4" class="border-b border-[#edf2f6] bg-slate-50 px-[0.9rem] py-[0.9rem]">
                                    <pre class="overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-relaxed text-slate-100">{{ $row->data ?? '' }}</pre>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                @include('admin.partials.table-empty', [
                                    'colspan' => 4,
                                    'title' => 'No log lines found',
                                    'message' => 'Try a different search term or log file.',
                                ])
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            @if ($lastPage > 1)
                <div class="border-t border-[#e5edf3] bg-[#fbfdff] px-4 py-4">
                    @include('partials.pagination', [
                        'currentPage' => $currentPage,
                        'lastPage' => $lastPage,
                        'label' => 'Log rows pagination',
                        'baseQuery' => array_filter([
                            'file' => $activeLogFile,
                            'q' => $q !== '' ? $q : null,
                            'limit' => $limit !== 50 ? $limit : null,
                        ]),
                    ])
                </div>
            @endif
        </section>
    </div>
@endsection
