@extends('auth.admin.layout')

@php
    $activeSection = 'plans';
@endphp

@section('content')
    @include('auth.admin.sections.plans.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/auth/admin/plans/' . $entity->uid),
        'method' => 'PUT',
    ])  
@endsection