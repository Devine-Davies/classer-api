@extends('admin.layout')

@php
    $activeSection = 'plans';
@endphp

@section('content')
    @include('admin.plans.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/admin/plans/' . $entity->uid),
        'method' => 'PUT',
    ])  
@endsection