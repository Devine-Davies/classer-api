@extends('admin.layout')

@php
    $activeSection = 'logs';

    $logs = collect($logs ?? []);
    $activeLogFile = $activeLogFile ?? $logs->first()['filename'] ?? 'laravel.log';
@endphp

@section('content')
    <header class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="m-0 text-admin-ink text-xl font-bold">Application Logs</h2>
            <p class="mt-[0.35rem] text-admin-muted">
                Review recent application activity and errors from your storage logs.
            </p>
        </div>

        <div class="flex items-center gap-2 rounded-full border border-admin-stroke bg-white px-3 py-2 text-sm text-admin-muted">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            <span id="logs-status">Ready</span>
        </div>
    </header>

    <section class="grid gap-4 lg:grid-cols-[18rem_1fr]">
        <aside class="overflow-hidden rounded-2xl border border-admin-stroke bg-white">
            <div class="border-b border-admin-stroke px-4 py-3">
                <h3 class="m-0 text-sm font-semibold text-admin-ink">Log files</h3>
                <p class="mt-1 text-xs text-admin-muted">Choose a file to inspect the latest 200 lines.</p>
            </div>

            <div class="max-h-[36rem] overflow-auto p-2">
                @forelse ($logs as $log)
                    @php
                        $filename = $log['filename'] ?? null;
                        $isActive = $filename === $activeLogFile;
                        $size = (int) ($log['size'] ?? 0);
                        $sizeLabel = $size >= 1048576
                            ? number_format($size / 1048576, 2).' MB'
                            : number_format(max($size, 0) / 1024, 1).' KB';
                    @endphp

                    @if ($filename)
                        <button
                            type="button"
                            data-log-file="{{ $filename }}"
                            class="log-file-button mb-1 w-full rounded-xl px-3 py-3 text-left transition hover:bg-slate-50 {{ $isActive ? 'bg-slate-100 ring-1 ring-admin-stroke' : '' }}"
                        >
                            <span class="block truncate text-sm font-semibold text-admin-ink">{{ $filename }}</span>
                            <span class="mt-1 flex items-center justify-between gap-2 text-xs text-admin-muted">
                                <span>{{ $sizeLabel }}</span>
                                @if (! empty($log['last_modified']))
                                    <span>{{ date('d M, H:i', (int) $log['last_modified']) }}</span>
                                @endif
                            </span>
                        </button>
                    @endif
                @empty
                    <div class="rounded-xl border border-dashed border-admin-stroke p-4 text-sm text-admin-muted">
                        No log files found in storage/logs.
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="overflow-hidden rounded-2xl border border-admin-stroke bg-white">
            <div class="flex flex-col gap-3 border-b border-admin-stroke px-4 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="m-0 text-sm font-semibold text-admin-ink">
                        <span id="active-log-name">{{ $activeLogFile }}</span>
                    </h3>
                    <p class="mt-1 text-xs text-admin-muted">Showing the latest 200 lines.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input
                        id="log-search"
                        type="search"
                        placeholder="Filter visible lines..."
                        class="min-w-[14rem] rounded-xl border border-admin-stroke bg-white px-3 py-2 text-sm text-admin-ink outline-none transition focus:border-slate-400"
                    >

                    <button
                        id="refresh-log"
                        type="button"
                        class="rounded-xl border border-admin-stroke bg-white px-3 py-2 text-sm font-semibold text-admin-ink transition hover:bg-slate-50"
                    >
                        Refresh
                    </button>
                </div>
            </div>

            <div class="logs-head grid grid-cols-[5.5rem_10rem_8rem_1fr] gap-3 border-b border-admin-stroke bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-admin-muted">
                <span>Level</span>
                <span>Time</span>
                <span>Context</span>
                <span>Message</span>
            </div>

            <div id="logs-container" class="max-h-[44rem] overflow-auto"></div>

            <div id="logs-empty" class="hidden px-4 py-12 text-center">
                <div class="mx-auto max-w-md rounded-2xl border border-dashed border-admin-stroke p-6">
                    <h4 class="m-0 text-sm font-semibold text-admin-ink">No log lines to show</h4>
                    <p class="mt-2 text-sm text-admin-muted">
                        This file may be empty, unavailable, or the current filter may not match any lines.
                    </p>
                </div>
            </div>
        </section>
    </section>

    <script type="text/template" id="logs-template">
        <details class="log-row border-b border-admin-stroke px-4 py-3 open:bg-slate-50" data-search="{search}">
            <summary class="grid cursor-pointer grid-cols-[5.5rem_10rem_8rem_1fr] gap-3 text-sm marker:hidden">
                <span class="pill {levelClass}">{type}</span>
                <span class="truncate text-admin-muted">{timestamp}</span>
                <span class="truncate text-admin-muted">{context}</span>
                <span class="log-message min-w-0 truncate text-admin-ink">{message}</span>
            </summary>
            <pre class="mt-3 overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-relaxed text-slate-100">{data}</pre>
        </details>
    </script>
@endsection

<script>
    const logsData = @json($logs->firstWhere('filename', $activeLogFile)['lines'] ?? []);
    const activeLogFile = @json($activeLogFile);
    const endpointTemplate = @json(route('api.admin.logs.show', ['filename' => '__filename__']));
    window.config = {
        logsData,
        activeLogFile,
        endpointTemplate,
        apiToken: @json(session('api_token')),
    };
</script>

 @vite('resources/views/admin/sections/logs/index.js')