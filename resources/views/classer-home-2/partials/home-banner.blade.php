{{-- We built the home for your memories --}}
@php
    $features = [
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>',
            'title' => 'Automatic',
            'desc'  => 'Plug in your drive and Classer Home does the sorting.',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
            'title' => 'Private by Design',
            'desc'  => 'Your footage stays in your home, never in the cloud.',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>',
            'title' => 'Made for rediscovery',
            'desc'  => 'Find any clip, moment or trip in seconds.',
        ],
    ];
@endphp

<div class="mx-auto w-full max-w-7xl px-6 md:px-8">

    <div class="relative overflow-hidden rounded-3xl">
        {{-- Background image --}}
        <img
            src="https://placeholders.io/1400/700"
            alt="Classer Home on a wooden cabinet"
            class="absolute inset-0 w-full h-full object-cover"
        />

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-black/10"></div>

        {{-- Content --}}
        <div class="relative px-6 md:px-12 py-16 md:py-24 lg:py-32 max-w-3xl">
            <h2 class="text-white text-3xl md:text-4xl lg:text-5xl leading-tight font-semibold mb-6">
                We built the home<br>
                for your memories
            </h2>
            <p class="text-gray-200 text-base md:text-lg leading-relaxed max-w-xl mb-12">
                A no-spell system that connects, organises and helps you rediscover the moments that matter.
            </p>

            {{-- Feature row --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-3xl">
                @foreach ($features as $f)
                    <div class="text-white">
                        <div class="text-white/90 mb-3">{!! $f['icon'] !!}</div>
                        <h3 class="text-base font-semibold mb-1">{{ $f['title'] }}</h3>
                        <p class="text-sm text-gray-300 leading-snug">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
