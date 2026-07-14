@extends('admin.layout')

@php
    $activeSection = 'posts';
@endphp

@section('content')
    @include('admin.sections.posts.partials.form', [
        'entity' => $entity ?? null,
        'action' => url('/admin/posts'),
        'method' => 'POST',
    ])
@endsection