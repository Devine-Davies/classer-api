

{{-- resources/views/gallery.blade.php --}}
@php
    // look over stories and transform to cards
    $cards = collect($stories)
        ->map(
            fn($story, $index) => [
                'id' => $index + 1,
                'title' => $story['title'] ?? 'Untitled',
                'img' => $story['thumbnail'] ?? 'https://picsum.photos/600/800', // fallback image
                'alt' => $story['alt'] ?? 'Gallery image',
                'desc' => $story['description'] ?? '', // optional description
                'permalink' => $story['permalink'] ?? '#', // optional link
                'date' => $story['date'] ?? null, // optional date
            ],
        )
        ->toArray();
@endphp

<style>
    /* --- Masonry (CSS Columns) --- */
    .masonry {
        column-gap: 1rem;
        /* space between columns */
    }

    /* Responsive column counts; adjust as you like */
    @media (min-width: 640px) {
        .masonry {
            column-count: 2;
        }
    }

    /* sm */
    @media (min-width: 1024px) {
        .masonry {
            column-count: 3;
        }
    }

    /* lg */
    @media (min-width: 1280px) {
        .masonry {
            column-count: 3;
        }
    }

    /* xl */

    /* Ensure items don’t split across columns */
    .masonry-item {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1rem;
        /* vertical gap */
    }

    /* Image loading fade-in */
    .fade-in {
        opacity: 0;
        transition: opacity .4s ease;
    }

    .fade-in.is-loaded {
        opacity: 1;
    }

    /* Optional: subtle card hover */
    .card-hover {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
    }
</style>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <header>
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">
            Our stories
        </h2>
    </header>
    <section id="masonry" class="masonry">
        @foreach ($cards as $card)
            <article class="masonry-item">
                <a href="{{ $card['permalink'] ?? '#' }}">
                    <div class="rounded-lg overflow-hidden bg-white shadow-sm ring-1 ring-black/5 card-hover">
                        <div class="relative">
                            <img src="{{ $card['img'] }}" alt="{{ $card['alt'] }}" loading="lazy" width="600"
                                height="800" class="w-full h-auto fade-in block">
                            <div class="absolute inset-x-0 bottom-0 p-3">
                                <span class="inline-block rounded bg-black/60 px-2 py-1 text-xs font-medium text-white">
                                    #{{ $card['id'] }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-base font-semibold leading-6">
                                {{ $card['title'] }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $card['desc'] }}
                            </p>
                            <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                                {{-- <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg>
                                    Photographer
                                </span> --}}
                                {{-- <span>·</span> --}}
                                <time datetime="{{ $card['date'] ?? '' }}">
                                    {{ $card['date'] ?? '' }}
                                </time>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @endforeach
    </section>
</div>

<script>
    // Fade-in images when they finish loading
    (function() {
        const imgs = document.querySelectorAll('#masonry img.fade-in');
        imgs.forEach(img => {
            if (img.complete) {
                img.classList.add('is-loaded');
            } else {
                img.addEventListener('load', () => img.classList.add('is-loaded'), {
                    once: true
                });
                img.addEventListener('error', () => img.classList.add('is-loaded'), {
                    once: true
                });
            }
        });
    })();
</script>
