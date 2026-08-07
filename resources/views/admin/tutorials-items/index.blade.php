@extends('admin.layout')

@php
    $activeSection = 'tutorials-items';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <div class="flex items-center justify-between gap-3 border-b border-[#e5edf3] bg-[#fbfdff] px-4 py-[0.9rem]">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Tutorial items</h2>
                <p class="text-sm text-slate-500">Manage the guided tutorial cards that appear on the public guides page.</p>
            </div>

            <a href="{{ route('admin.tutorials-items.add') }}" class="btn-outline-invert inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold">
                Add item
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Label</th>
                        <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">URL</th>
                        <th class="text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Sort order</th>
                        <th class="text-right text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr id="tutorial-item-row-{{ $item['id'] }}" class="scroll-mt-24">
                            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top">
                                <span class="font-semibold text-slate-900">{{ $item['label'] ?? '-' }}</span>
                            </td>
                            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top">
                                <span class="break-all">{{ $item['url'] ?? '-' }}</span>
                            </td>
                            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top">
                                {{ $item['sortOrder'] ?? 0 }}
                            </td>
                            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top text-right">
                                @include('admin.partials.table-actions-dropdown', [
                                    'buttonLabel' => 'Options',
                                    'items' => [
                                        [
                                            'label' => 'Edit',
                                            'url' => route('admin.tutorials-items.edit', ['itemId' => $item['id']]),
                                            'method' => 'GET',
                                            'color' => 'slate',
                                        ],
                                    ],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @include('admin.partials.table-empty', [
                                'colspan' => 4,
                                'title' => 'No tutorial items found',
                                'message' => 'Add items to the JSON file to populate the guides page.',
                            ])
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
