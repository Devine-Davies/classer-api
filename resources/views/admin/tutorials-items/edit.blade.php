@extends('admin.layout')

@php
    $activeSection = 'tutorials-items';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.tutorials-items.partials.form', [
            'item' => $item,
            'isEdit' => true,
            'action' => route('admin.tutorials-items.update', ['itemId' => $item['id']]),
            'method' => 'PUT',
        ])
    </section>
@endsection
