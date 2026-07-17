@extends('admin.layout')

@php
    $activeSection = 'products';
@endphp

@section('content')
    @include('admin.products.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/admin/products/' . $entity->uid),
        'method' => 'PUT',
    ]);
@endsection
