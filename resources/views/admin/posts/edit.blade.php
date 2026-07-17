@extends('admin.layout')

@php
    $activeSection = 'posts';
@endphp

@section('content')
    @include('admin.posts.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/admin/posts/' . $entity->uid),
        'method' => 'PUT',
    ])
@endsection