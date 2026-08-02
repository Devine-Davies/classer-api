@props([
    'cards' => [],
    'title' => 'See our community stories',
    'intro' => 'The adventures behind the footage.',
])

@php
    $cards = collect($cards)->map(fn ($card) => (object) $card);
@endphp

@once
    @vite('resources/views/components/card-carousel/card-carousel.css')
    @vite('resources/views/components/card-carousel/card-carousel.js')
@endonce
    
<section {{ $attributes->merge(['class' => 'w-full max-w-full overflow-hidden']) }}>
    <div class="mx-auto max-w-screen-md text-center px-4">
        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color text-absolute font-medium leading-[108.54%] text-center">
            {{ $title }}
        </h2>

        @if ($intro)
            <p class="text-base text-slate-600 py-3 lg:pb-8">
                {{ $intro }}
            </p>
        @endif
    </div>

    <div class="w-full max-w-full">
        <div
            data-drag-scroll
            class="card-scroll flex w-full max-w-full cursor-grab select-none gap-4 overflow-x-auto overflow-y-hidden pb-12 sm:px-10 lg:pl-[25%]"
        >
            @foreach ($cards as $card)
                <article class="flex-none w-[calc(100%/1.5)] sm:w-[calc(100%/2.5)] md:w-[calc(100%/2.5)] lg:w-[calc(100%/2)] max-w-[400px]">
                    <div class="aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
                        <img
                            src="{{ $card->thumbnail }}"
                            alt="{{ $card->title }}"
                            class="pointer-events-none h-full w-full object-cover"
                            loading="lazy"
                            draggable="false"
                        >
                    </div>

                    <div class="mt-3">
                        <h3 class="text-base font-semibold leading-tight text-brand-color">
                            @if (! empty($card->permalink))
                                <a href="{{ $card->permalink }}" class="hover:underline">
                                    {{ $card->title }}
                                </a>
                            @else
                                {{ $card->title }}
                            @endif
                        </h3>

                        @if (! empty($card->description))
                            <p class="mt-1 text-sm italic leading-snug text-slate-500">
                                “{{ $card->description }}”
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
