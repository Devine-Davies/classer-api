@extends('admin.layout')

@php
    $activeSection = 'stats';
    $dotClass = ['bg-green-500', 'bg-blue-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500'];
@endphp

@section('content')
    <div class="space-y-5">
        @foreach ($stats as $group)
            <section class="border border-[#dde4ea] rounded-[0.95rem] bg-white p-4">
                <header class="mb-3">
                    <h3 class="m-0 text-[1rem] font-bold text-admin-ink">{{ $group['label'] }}</h3>
                    <p class="mt-[0.2rem] text-[0.82rem] text-admin-muted">{{ $group['description'] }}</p>
                </header>

                <div id="stats-container-{{ $group['key'] }}" class="grid grid-cols-2 gap-[0.9rem]">
                    @foreach ($group['items'] as $stat)
                        @if (! empty($stat['details_url']))
                            <a href="{{ $stat['details_url'] }}" class="border border-[#dde4ea] rounded-[0.9rem] bg-[#fbfdff] p-4 flex items-center gap-3 no-underline transition hover:border-[#b8dfdc] hover:bg-[#f7fcfb]">
                                <div class="w-[0.8rem] h-[0.8rem] rounded-full shrink-0 {{ $dotClass[$loop->index % count($dotClass)] }}"></div>
                                <div>
                                    <p class="m-0 text-[0.76rem] text-[#66717a] uppercase tracking-[0.02em]">{{ $stat['label'] }}</p>
                                    <h3 class="mt-[0.2rem] text-[1.3rem] text-[#162127]">{{ $stat['formatted'] }}</h3>
                                </div>
                            </a>
                        @else
                            <article class="border border-[#dde4ea] rounded-[0.9rem] bg-[#fbfdff] p-4 flex items-center gap-3">
                                <div class="w-[0.8rem] h-[0.8rem] rounded-full shrink-0 {{ $dotClass[$loop->index % count($dotClass)] }}"></div>
                                <div>
                                    <p class="m-0 text-[0.76rem] text-[#66717a] uppercase tracking-[0.02em]">{{ $stat['label'] }}</p>
                                    <h3 class="mt-[0.2rem] text-[1.3rem] text-[#162127]">{{ $stat['formatted'] }}</h3>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
