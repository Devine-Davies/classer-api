@extends('auth.admin.layout')

@php
    $activeSection = 'bulk-mails';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Bulk Email Templates</h2>
        <p class="mt-[0.35rem] text-admin-muted">Select a template and queue emails for a list of user addresses.</p>
    </header>

    <div class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)]">
        @include('partials.admin.bulk-mails')
    </div>
@endsection
