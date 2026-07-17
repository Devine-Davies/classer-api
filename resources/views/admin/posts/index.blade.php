@extends('admin.layout')

@php
    $activeSection = 'posts';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $q = $filters['q'] ?? request('q', '');
    $cache = $cache ?? ['exists' => false, 'count' => 0, 'generated_at' => null];
    $cacheGeneratedAt = $cache['generated_at'] ?? null;
    $cacheGeneratedAtLabel = $cacheGeneratedAt ? \Illuminate\Support\Carbon::parse($cacheGeneratedAt)->format('d M Y H:i') : 'Never';

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <div class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]">
            <form method="GET" action="" class="flex items-center gap-[0.65rem] flex-wrap" id="posts-filter-form">
                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="posts-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="posts-search" name="q" type="search" placeholder="Search by title, slug, author, or ID"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._postsSearchTimer); window._postsSearchTimer = setTimeout(() => document.getElementById('posts-filter-form').submit(), 300)">
                </label>
            </form>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.posts.refresh-cache') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-[#c9d6e2] bg-white px-3 py-2 text-sm font-semibold text-[#314353] hover:bg-[#f4f8fb]">
                        Rebuild cache from S3
                    </button>
                </form>

                <a href="{{ url('/admin/posts/add') }}" class="rounded-xl bg-admin-primary px-4 py-2.5 text-sm font-semibold">
                    Add post
                </a>
            </div>
        </div>

        <div class="px-4 py-2 text-[0.78rem] text-[#667789] border-b border-[#edf2f6] bg-white">
            Cache: {{ $cache['count'] ?? 0 }} posts · Last full scan: {{ $cacheGeneratedAtLabel }}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Title</th>
                        <th class="{{ $thClass }}">Slug</th>
                        <th class="{{ $thClass }}">Type</th>
                        <th class="{{ $thClass }}">Author</th>
                        <th class="{{ $thClass }}">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $post)
                        <tr>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link" href="{{ url('/admin/posts/' . urlencode($post->uid)) }}">
                                    <span class="orders-code">{{ $post->title ?? '-' }}</span>
                                </a>
                                <div class="mt-1 text-[0.74rem] text-slate-500 font-mono">{{ $post->uid ?? '-' }}</div>
                            </td>

                            <td class="{{ $tdClass }}">
                                <a class="orders-link" href="{{ $post->permalink ?? '#' }}" target="_blank" rel="noreferrer">
                                    <span class="orders-code">{{ $post->slug ?? '-' }}</span>
                                </a>
                            </td>

                            <td class="{{ $tdClass }}">{{ ucfirst($post->type ?? '-') }}</td>
                            <td class="{{ $tdClass }}">{{ $post->author ?? '-' }}</td>
                            <td class="{{ $tdClass }}">{{ $post->dateFormatted ?? ($post->date ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="orders-empty">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.pagination', [
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'label' => 'Posts pagination',
                'baseQuery' => array_filter([
                    'q' => $q ?: null,
                ]),
            ])
        @endif
    </section>
@endsection
