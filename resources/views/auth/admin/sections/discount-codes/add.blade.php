@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <section class="admin-card max-w-3xl admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('auth.admin.sections.discount-codes.partials.form', [
            'discountCode' => $entity ?? null,
            'catalogItems' => $catalogItems ?? collect(),
            'isEdit' => false,
            'action' => url('/auth/admin/discount-codes'),
            'method' => 'POST',
            'submitLabel' => 'Create discount code',
            'cancelUrl' => url('/auth/admin/discount-codes'),
            'showDisable' => false,
        ])
    </section>
@endsection