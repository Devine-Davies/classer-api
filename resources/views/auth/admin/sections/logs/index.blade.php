@extends('auth.admin.layout')

@php
    $activeSection = 'logs';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Application Logs</h2>
        <p class="mt-[0.35rem] text-admin-muted">Latest records from storage logs.</p>
    </header>

    <section class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)] logs-table">
        <div class="logs-head">
            <span>Level</span>
            <span>Time</span>
            <span>Context</span>
            <span>Message</span>
        </div>
        <div id="logs-container"></div>
    </section>

    <script type="text/template" id="logs-template">
        <details class="log-row">
            <summary>
                <span class="log-level {levelClass}">{type}</span>
                <span>{timestamp}</span>
                <span>{context}</span>
                <span class="log-message">{message}</span>
            </summary>
            <pre>{data}</pre>
        </details>
    </script>
@endsection
