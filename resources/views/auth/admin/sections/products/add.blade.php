@extends('auth.admin.layout')

@php
    $activeSection = 'products';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <div>
            <h2>Create Product</h2>
            <p>Add a new product used for subscriptions and checkout.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ url('/auth/admin/products') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
            >
                Back
            </a>
        </div>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        @include('auth.admin.sections.products._form', [
            'product' => $entity ?? null,
            'isEdit' => false,
            'action' => url('/auth/admin/products'),
            'method' => 'POST',
            'submitLabel' => 'Create product',
            'cancelUrl' => url('/auth/admin/products'),
            'showDelete' => false,
        ])
    </section>
@endsection