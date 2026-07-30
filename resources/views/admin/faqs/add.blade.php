@extends('admin.layout')

@php
    $activeSection = 'faqs';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.faqs.partials.form', [
            'faq' => null,
            'isEdit' => false,
            'action' => url('/admin/faqs'),
            'method' => 'POST',
        ])
    </section>
@endsection
