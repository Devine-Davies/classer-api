@extends('auth.admin.layout')

@php
    $activeSection = 'products';
@endphp

@section('content')
    @include('auth.admin.sections.products.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/auth/admin/products/' . $entity->uid),
        'method' => 'PUT',
    ]);
@endsection
