@extends('admin.layout')

@php
    $activeSection = 'faqs';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $q = $filters['q'] ?? request('q', '');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem] align-top';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="faqs-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="faqs-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="faqs-search" name="q" type="search" placeholder="Search by question, answer, or category"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._faqsSearchTimer); window._faqsSearchTimer = setTimeout(() => document.getElementById('faqs-filter-form').submit(), 300)">
                </label>
            </div>

            <a href="{{ url('/admin/faqs/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold">
                Add FAQ
            </a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Question</th>
                        <th class="{{ $thClass }}">Category</th>
                        <th class="{{ $thClass }}">Order</th>
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Updated At</th>
                        <th class="{{ $thClass }} text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $faq)
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link" href="{{ url('/admin/faqs/' . $faq->uid) }}">
                                    <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Str::limit($faq->question ?? '-', 80) }}</span>
                                </a>
                            </td>
                            <td class="{{ $tdClass }}">
                                @if ($faq->category)
                                    <span class="pill slate">{{ $faq->category }}</span>
                                @else
                                    <span class="text-sm text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">{{ $faq->sortOrder ?? 0 }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($faq->isPublished)
                                    <span class="pill emerald">Published</span>
                                @else
                                    <span class="pill amber">Unpublished</span>
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">{{ $faq->updatedAtFormatted ?? '-' }}</td>
                            <td class="{{ $tdClass }} text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.faqs.publish', ['faqUid' => $faq->uid]) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded-lg border border-[#d9e4ec] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#334155] transition hover:border-[#94a3b8] hover:bg-[#f8fafc]">
                                            {{ $faq->isPublished ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>

                                    <a href="{{ url('/admin/faqs/' . $faq->uid) }}"
                                       class="inline-flex items-center justify-center rounded-lg border border-[#d9e4ec] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#334155] transition hover:border-[#94a3b8] hover:bg-[#f8fafc]">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @include('admin.partials.table-empty', [
                                'colspan' => 6,
                                'title' => 'No FAQs found',
                                'message' => 'Try adjusting your search, or add a new FAQ.',
                            ])
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'FAQs pagination',
                'baseQuery'   => array_filter([
                    'q' => $q ?: null,
                ]),
            ])
        @endif
    </section>
@endsection
