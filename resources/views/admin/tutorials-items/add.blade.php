@extends('admin.layout')

@php
    $activeSection = 'tutorials-items';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.tutorials-items.partials.form', [
            'item' => null,
            'isEdit' => false,
            'action' => route('admin.tutorials-items.create'),
            'method' => 'POST',
        ])
    </section>
@endsection