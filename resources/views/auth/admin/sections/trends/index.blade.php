@extends('auth.admin.layout')

@php
    $activeSection = 'trends';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Trends</h2>
        <p>Analyze key product metrics over time with custom range and interval controls.</p>
    </header>

    <section class="trends-panel">
        <div class="trends-controls">
            <label>
                <span>Start Date</span>
                <input id="trends-start-date" type="date" />
            </label>
            <label>
                <span>End Date</span>
                <input id="trends-end-date" type="date" />
            </label>
            <label>
                <span>Interval</span>
                <select id="trends-interval">
                    <option value="hourly">Hourly</option>
                    <option value="daily" selected>Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </label>
            <button id="trends-apply" type="button">Apply</button>
        </div>

        <div class="trends-domain-tabs" role="tablist" aria-label="Trend domains">
            <button type="button" class="trend-domain is-active" data-trend-domain="users">Users</button>
            <button type="button" class="trend-domain" data-trend-domain="subscriptions">Subscriptions</button>
            <button type="button" class="trend-domain" data-trend-domain="cloudShares">Cloud Share</button>
            <button type="button" class="trend-domain" data-trend-domain="logins">Logins</button>
        </div>

        <p id="trends-status" class="trends-status" aria-live="polite"></p>
        <div id="trends-chart" class="trends-chart" role="img" aria-label="Trends chart"></div>
    </section>
@endsection
