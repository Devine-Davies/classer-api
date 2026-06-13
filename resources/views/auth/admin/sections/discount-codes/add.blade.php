@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <h2>Create Discount Code</h2>
        <a
            href="{{ url('/auth/admin/discount-codes') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
        >
            Back to discount codes
        </a>
    </header>

    <section class="admin-card p-5 max-w-3xl">
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