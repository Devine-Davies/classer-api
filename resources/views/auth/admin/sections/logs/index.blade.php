@extends('auth.admin.layout')

@php
    $activeSection = 'logs';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Application Logs</h2>
        <p>Latest records from storage logs.</p>
    </header>

    <section class="admin-card logs-table">
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
