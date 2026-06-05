@extends('auth.admin.layout')

@php
    $activeSection = 'bulk-mails';
@endphp

@section('content')
    <header class="admin-section-header">
        <h2>Bulk Email Templates</h2>
        <p>Select a template and queue emails for a list of user addresses.</p>
    </header>

    <div class="admin-card">
        @include('partials.admin.bulk-mails')
    </div>
@endsection
