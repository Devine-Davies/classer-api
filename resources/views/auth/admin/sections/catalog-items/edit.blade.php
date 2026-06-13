@extends('auth.admin.layout')

@php
    $activeSection = 'catalog-items';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <div>
            <h2>Edit Catalog Item</h2>
            <p>Update commercial catalog attributes used by checkout and discounts.</p>
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
        <div class="mb-5 flex items-center justify-between gap-3 border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ $entity->title ?? 'Catalog item' }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $entity->sku ?? '' }}

                    @if (! empty($entity->currency) && isset($entity->price_amount))
                        · {{ strtoupper($entity->currency) }} {{ number_format($entity->price_amount / 100, 2) }}
                    @endif
                </p>
            </div>

            @if ($entity->is_active ?? false)
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Active
                </span>
            @else
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    Inactive
                </span>
            @endif
        </div>

        @include('auth.admin.sections.catalog-items._form', [
            'catalogItem' => $entity,
            'products' => $products ?? collect(),
            'plans' => $plans ?? collect(),
            'isEdit' => true,
            'action' => url('/auth/admin/catalog-items/' . $entity->uid),
            'method' => 'PUT',
            'submitLabel' => 'Update catalog item',
            'cancelUrl' => url('/auth/admin/catalog-items'),
        ])
    </section>
@endsection