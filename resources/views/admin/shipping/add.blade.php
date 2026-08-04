@extends('admin.layout')

@php
    $activeSection = 'shipping';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.shipping.partials.form', [
            'item' => null,
            'isEdit' => false,
            'action' => route('admin.shipping.create'),
            'method' => 'POST',
        ])
    </section>
@endsection
