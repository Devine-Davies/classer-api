@extends('admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <section class="admin-card max-w-3xl admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.discount-codes.partials.form', [
            'discountCode' => $entity,
            'catalogItems' => $catalogItems ?? collect(),
            'isEdit' => true,
            'action' => url('/admin/discount-codes/' . $entity->uid),
            'method' => 'PUT',
            'submitLabel' => 'Update discount code',
            'cancelUrl' => url('/admin/discount-codes'),
            'disableUrl' => url('/admin/discount-codes/' . $entity->uid . '/disable'),
            'showDisable' => true,
        ])
    </section>
@endsection