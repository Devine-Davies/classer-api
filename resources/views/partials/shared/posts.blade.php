@php
// look over posts and transform to cards
$cards = collect($posts)
->map(
fn($post, $index) => [
'id' => $index + 1,
'title' => $post['title'] ?? 'Untitled',
'img' => $post['thumbnail'] ?? 'https://picsum.photos/600/800', // fallback image
'alt' => $post['alt'] ?? 'Gallery image',
'desc' => $post['description'] ?? '', // optional description
'permalink' => $post['permalink'] ?? '#', // optional link
'date' => $post['date'] ?? null, // optional date
],
)
->toArray();
@endphp

<style>
    /* --- Masonry (CSS Columns) --- */
    .masonry {
        column-gap: 1rem;
    }

    .masonry-item {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }

    @media (min-width: 480px) {
        .masonry {
            column-count: 2;
        }
    }

    /* Responsive column counts; adjust as you like */
    @media (min-width: 640px) {
        .masonry {
            column-count: 3;
        }

        .masonry.offset-y .masonry-item:nth-child(2n+3) img {
            margin-top: 15%;
        }
    }

    /* sm */
    @media (min-width: 1024px) {
        .masonry {
            column-count: 3;
        }

        .masonry.story-posts .masonry-item:nth-child(2n+3) img {
            margin-top: 20%;
        }
    }

    /* lg */
    @media (min-width: 1280px) {
        .masonry {
            column-count: 4;
        }

        .masonry.offset-y {
            column-count: 3;
        }

        .masonry.offset-y .masonry-item:nth-child(2n+3) img {
            margin-top: 27%;
        }

        .masonry.offset-y .masonry-item:nth-child(4n+4) img {
            margin-top: 10%;
        }
    }

    .fade-in {
        opacity: 0;
        transition: opacity .4s ease;
    }

    .fade-in.is-loaded {
        opacity: 1;
    }

    .card-hover {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
    }
</style>

<div class="mx-auto max-w-7xl py-8">
    <header>
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">
            {{ $title ?? 'No Title ' }}
        </h2>
    </header>
    <section class="masonry {{ $masonryType ?? '' }}">
        @foreach ($cards as $card)
        <article class="h-full flex masonry-item group">
            <a href="{{ $card['permalink'] ?? '#' }}">
                <div class="flex h-full rounded-lg overflow-hidden bg-white shadow-sm ring-1 ring-black/5 card-hover">
                    <div class="relative" style="background-image: url('{{ $card['img'] }}'); background-size: cover; background-position: center; width: 100%; height: 100%;">
                        <img src="{{ $card['img'] }}" alt="{{ $card['alt'] }} hidden" loading="lazy" class="w-full top-0 h-full block object-cover opacity-0" />
                        <div class="absolute z-10 inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/90 to-transparent backdrop-blur-sm">
                            <h3 class="text-base md:text-xl lg:text-2xl lg:pb-4 font-semibold leading-6 inline-block rounded text-white opacity-50 group-hover:opacity-100 transition-opacity duration-200">
                                {{ $card['title'] }}
                            </h3>
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
        const imgs = document.querySelectorAll('img.fade-in');
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


{{-- <p class="mt-1 text-sm text-gray-500">
    {{ $card['desc'] }}
</p> --}}
{{-- <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
    <span class="inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg>
        Photographer
    </span>
    <span>·</span>
    <time datetime="{{ $card['date'] ?? '' }}">
{{ $card['date'] ?? '' }}
</time>
</div> --}}