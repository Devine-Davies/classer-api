@extends('auth.admin.layout')

@php
    $action = url('/auth/admin/plans');
    $activeSection = 'plans';
@endphp

@section('content')
    @include('auth.admin.sections.plans.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/auth/admin/plans'),
        'method' => 'POST',
    ])
@endsection