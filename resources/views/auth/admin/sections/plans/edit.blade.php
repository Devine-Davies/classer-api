@extends('auth.admin.layout')

@php
    $activeSection = 'plans';
@endphp

@section('content')
    <header class="admin-section-header flex flex-col items-start gap-3 sm:flex-row sm:justify-between max-w-3xl">
        <div>
            <h2>Edit Plan</h2>
            <p>Update the plan details used for subscriptions and checkout.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ url('/auth/admin/plans') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
            >
                Back
            </a>
        </div>
    </header>

    <section class="admin-card max-w-3xl p-5">
        @include('auth.admin.sections.plans._form', [
            'plan' => $entity,
            'isEdit' => true,
            'action' => url('/auth/admin/plans/' . $entity->uid),
            'method' => 'PUT',
            'submitLabel' => 'Update plan',
            'cancelUrl' => url('/auth/admin/plans'),
        ])
    </section>
@endsection 