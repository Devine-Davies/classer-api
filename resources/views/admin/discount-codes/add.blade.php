@extends('admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <section class="admin-card max-w-3xl admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.discount-codes.partials.form', [
            'discountCode' => $entity ?? null,
            'catalogItems' => $catalogItems ?? collect(),
            'isEdit' => false,
            'action' => url('/admin/discount-codes'),
            'method' => 'POST',
            'submitLabel' => 'Create discount code',
            'cancelUrl' => url('/admin/discount-codes'),
            'showDisable' => false,
        ])
    </section>
@endsection