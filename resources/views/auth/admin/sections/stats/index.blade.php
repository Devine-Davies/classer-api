@extends('auth.admin.layout')

@php
    $activeSection = 'stats';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Team Stats</h2>
        <p>Live snapshot from the admin stats endpoint.</p>
    </header>

    <div id="stats-container" class="stats-grid"></div>

    <script type="text/template" id="stats-template">
        <article class="stats-card">
            <div class="stats-card-dot {dotClass}"></div>
            <div>
                <p class="stats-card-title">{title}</p>
                <h3 class="stats-card-value">{stat}</h3>
            </div>
        </article>
    </script>
@endsection
