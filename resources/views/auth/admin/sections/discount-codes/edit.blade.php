@extends('auth.admin.layout')

@php
    $activeSection = 'discount-codes';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <h2>Edit Discount Code</h2>
        <a
            href="{{ url('/auth/admin/discount-codes') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
        >
            Back to discount codes
        </a>
    </header>

    <section class="admin-card p-5 max-w-3xl">
        <div class="mb-5 flex items-center justify-between gap-3 border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ $entity->code ?? 'Discount code' }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $entity->discount_percentage ?? 0 }}% discount

                    @if (! empty($entity->assigned_email))
                        · Assigned to {{ $entity->assigned_email }}
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