@extends('admin.layout')

@php
    $activeSection = 'currencies';
@endphp

@section('content')
    <section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
        @include('admin.currencies.partials.form', [
            'item' => $item,
            'isEdit' => true,
            'action' => route('admin.currencies.update', ['currencyRow' => $item['_row']]),
            'method' => 'PUT',
        ])
    </section>

    <section class="admin-card max-w-3xl overflow-hidden">
        <div class="p-5 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Delete currency</h3>
                <p class="mt-1 text-sm text-slate-500">This removes the selected row from currencies.json.</p>
            </div>

            <form method="POST" action="{{ route('admin.currencies.destroy', ['currencyRow' => $item['_row']]) }}" onsubmit="return confirm(@js('Delete this currency? This cannot be undone.'))">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirmDelete" value="DELETE">
                <button
                    type="submit"
                    class="inline-flex justify-center items-center py-2 px-4 text-sm font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-md shadow-sm hover:bg-rose-100 focus:ring-2 focus:ring-rose-300 focus:ring-offset-2"
                >
                    Delete
                </button>
            </form>
        </div>
    </section>
@endsection