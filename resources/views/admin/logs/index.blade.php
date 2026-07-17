@extends('admin.layout')

@php
    $activeSection = 'logs';
    $logs = collect($logs ?? []);
    $activeLogFile = $activeLogFile ?? $logs->first()['filename'] ?? null;
    $rows = collect($rows ?? []);

    $currentPage = $pagination['current_page'] ?? 1;
    $lastPage = $pagination['last_page'] ?? 1;
    $from = $pagination['from'] ?? 0;
    $to = $pagination['to'] ?? 0;
    $total = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');
    $limit = (int) ($filters['limit'] ?? request('limit', 50));

    $primaryFilenames = collect(['laravel.log', 'app.log']);
    $primaryLogs = $primaryFilenames
        ->map(fn (string $filename) => $logs->firstWhere('filename', $filename))
        ->filter();
    $otherLogs = $logs->filter(
        fn ($log) => ! $primaryFilenames->contains($log['filename'] ?? '')
    )->values();

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
@endphp

@section('content')
    <header class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="m-0 text-admin-ink text-xl font-bold">Application Logs</h2>
            <p class="mt-[0.35rem] text-admin-muted">
                Review server log lines with file tabs and server-side pagination.
            </p>
        </div>
    </header>

    <section class="border border-admin-stroke bg-white" x-data="{ openClearModal: false, confirmFile: '' }">
        <div class="border-b border-[#e5edf3] bg-[#fbfdff] px-4 py-[0.9rem] space-y-3">
            @if ($primaryLogs->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($primaryLogs as $log)
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
                                class="inline-flex items-center gap-2 rounded-[0.7rem] border px-[0.9rem] py-[0.6rem] text-[0.84rem] font-bold transition {{ $isActive ? 'border-[#8dcfc8] bg-[#eaf8f6] text-[#0f6d62]' : 'border-[#cfdbe4] bg-white text-[#344553] hover:border-[#b8c7d2]' }}"
                            >
                                <span>{{ $filename }}</span>
                                <span class="text-[0.72rem] {{ $isActive ? 'text-[#0f6d62]/80' : 'text-[#6f7c89]' }}">{{ $sizeLabel }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                @forelse ($otherLogs as $log)
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
                            class="inline-flex items-center gap-2 rounded-[0.65rem] border px-[0.7rem] py-[0.5rem] text-[0.8rem] font-semibold transition {{ $isActive ? 'border-[#b8dfdc] bg-admin-primary-soft text-admin-primary' : 'border-[#d8e2ea] bg-white text-[#3b4a56] hover:border-[#bfcdda]' }}"
                        >
                            <span class="truncate max-w-[14rem]">{{ $filename }}</span>
                            <span class="text-[0.72rem] {{ $isActive ? 'text-admin-primary/80' : 'text-[#6f7c89]' }}">{{ $sizeLabel }}</span>
                        </a>
                    @endif
                @empty
                    @if ($primaryLogs->isEmpty())
                        <p class="m-0 text-sm text-admin-muted">No log files found in storage/logs.</p>
                    @endif
                @endforelse
            </div>
        </div>

        <form method="GET" action="{{ route('admin.logs') }}"
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="logs-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                @if ($activeLogFile)
                    <input type="hidden" name="file" value="{{ $activeLogFile }}">
                @endif

                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="logs-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="logs-search" name="q" type="search" placeholder="Search lines"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._logsSearchTimer); window._logsSearchTimer = setTimeout(() => document.getElementById('logs-filter-form').submit(), 300)">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="logs-limit-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Rows</span>
                    <select id="logs-limit-filter" name="limit"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('logs-filter-form').submit()">
                        <option value="25" @selected($limit === 25)>25</option>
                        <option value="50" @selected($limit === 50)>50</option>
                        <option value="100" @selected($limit === 100)>100</option>
                        <option value="200" @selected($limit === 200)>200</option>
                    </select>
                </label>
            </div>

            <div class="flex items-center gap-2">
                @if ($activeLogFile)
                    <button
                        type="button"
                        x-on:click="openClearModal = true"
                        class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                    >
                        Clear log file
                    </button>
                @endif

                <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                    @if ($total)
                        {{ $from }}&ndash;{{ $to }} of {{ number_format($total) }}
                    @else
                        0 results
                    @endif
                </p>
            </div>
        </form>

        @if ($activeLogFile)
            <template x-teleport="body">
                <div
                    x-show="openClearModal"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/50 px-4"
                    x-on:keydown.escape.window="openClearModal = false"
                >
                    <div
                        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl"
                        x-on:click.outside="openClearModal = false"
                    >
                        <h4 class="text-base font-bold text-slate-900">Clear log file</h4>
                        <p class="mt-2 text-sm text-slate-600">
                            This will remove all content from
                            <span class="font-bold text-slate-900">{{ $activeLogFile }}</span>
                            and cannot be undone.
                        </p>

                        <form id="clear-log-form" method="POST" action="{{ route('admin.logs.clear') }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="file" value="{{ $activeLogFile }}">
                            <input type="hidden" name="q" value="{{ $q }}">
                            <input type="hidden" name="limit" value="{{ $limit }}">

                            <div>
                                <label class="block text-sm font-semibold text-slate-700" for="logs-confirm-file">
                                    Type <span class="font-bold text-slate-900">{{ $activeLogFile }}</span> to confirm
                                </label>
                                <input
                                    id="logs-confirm-file"
                                    name="confirm_file"
                                    type="text"
                                    x-model="confirmFile"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-500/10"
                                    placeholder="{{ $activeLogFile }}"
                                    autocomplete="off"
                                    required
                                >
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    x-on:click="openClearModal = false"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    x-bind:disabled="confirmFile !== @js($activeLogFile)"
                                    class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Confirm clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif

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
                                    <span class="text-[0.72rem] font-semibold uppercase tracking-[0.04em] text-[#8391a0]" x-text="open ? 'Hide' : 'View'"></span>
                                </div>
                            </td>
                        </tr>
                        <tr x-cloak x-show="open" x-transition.opacity.duration.120ms>
                            <td colspan="4" class="px-[0.9rem] py-[0.9rem] border-b border-[#edf2f6] bg-slate-50">
                                <pre class="overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-relaxed text-slate-100">{{ $row->data ?? '' }}</pre>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="4" class="orders-empty">No log lines match this filter.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        @if ($lastPage > 1)
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
        @endif
    </section>
@endsection
