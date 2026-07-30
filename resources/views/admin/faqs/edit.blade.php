@extends('admin.layout')

@php
    $activeSection = 'faqs';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.faqs.partials.form', [
            'faq' => $entity,
            'isEdit' => true,
            'action' => url('/admin/faqs/' . $entity->uid),
            'method' => 'PUT',
        ])
    </section>
@endsection
