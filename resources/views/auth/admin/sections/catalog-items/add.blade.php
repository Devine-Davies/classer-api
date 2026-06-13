@extends('auth.admin.layout')

@php
    $activeSection = 'catalog-items';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <div>
            <h2>Create Catalog Item</h2>
            <p>Add commercial catalog attributes used by checkout and discounts.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ url('/auth/admin/catalog-items') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
            >
                Back to catalog items
            </a>
        </div>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        @include('auth.admin.sections.catalog-items._form', [
            'catalogItem' => $entity ?? null,
            'products' => $products ?? collect(),
            'plans' => $plans ?? collect(),
            'isEdit' => false,
            'action' => url('/auth/admin/catalog-items'),
            'method' => 'POST',
            'submitLabel' => 'Create catalog item',
            'cancelUrl' => url('/auth/admin/catalog-items'),
        ])
    </section>
@endsection