@extends('admin.layout')

@php
    $activeSection = 'currencies';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.currencies.partials.form', [
            'item' => null,
            'isEdit' => false,
            'action' => route('admin.currencies.create'),
            'method' => 'POST',
        ])
    </section>
@endsection