@extends('admin.layout')

@php
    $action = url('/admin/plans');
    $activeSection = 'plans';
@endphp

@section('content')
    @include('admin.sections.plans.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/admin/plans'),
        'method' => 'POST',
    ])
@endsection