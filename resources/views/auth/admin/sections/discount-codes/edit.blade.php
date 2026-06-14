@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <section class="admin-card max-w-3xl">
        @include('auth.admin.sections.discount-codes._form', [
            'discountCode' => $entity,
            'catalogItems' => $catalogItems ?? collect(),
            'isEdit' => true,
            'action' => url('/auth/admin/discount-codes/' . $entity->uid),
            'method' => 'PUT',
            'submitLabel' => 'Update discount code',
            'cancelUrl' => url('/auth/admin/discount-codes'),
            'disableUrl' => url('/auth/admin/discount-codes/' . $entity->uid . '/disable'),
            'showDisable' => true,
        ])
    </section>
@endsection