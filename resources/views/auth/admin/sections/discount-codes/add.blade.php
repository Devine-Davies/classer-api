@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <section class="admin-card max-w-3xl">
        @include('auth.admin.sections.discount-codes._form', [
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