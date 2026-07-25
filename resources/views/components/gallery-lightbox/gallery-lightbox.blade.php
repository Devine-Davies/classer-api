@once
    @vite('resources/views/components/gallery-lightbox/gallery-lightbox.css')
@endonce

@props([
    'openState' => 'lightboxOpen',
    'galleryState' => 'gallery',
    'indexState' => 'activeImage',
    'closeAction' => 'closeLightbox()',
    'nextAction' => 'next()',
    'prevAction' => 'prev()',
])

<div
    x-cloak
    x-show="{{ $openState }}"
    x-transition.opacity
    class="pg-lightbox"
    @click.self="{{ $closeAction }}"
    @keydown.escape.window="{{ $closeAction }}"
    @keydown.arrow-right.window="if ({{ $openState }}) {{ $nextAction }}"
    @keydown.arrow-left.window="if ({{ $openState }}) {{ $prevAction }}"
>
    <div class="pg-lightbox__backdrop"></div>

    <div class="pg-lightbox__container">
        <button
            type="button"
            class="pg-lightbox__close"
            @click="{{ $closeAction }}"
            aria-label="Close lightbox"
        >
            @icon('lightbox-close')
        </button>

        <div class="pg-lightbox__panel">
            <div class="pg-lightbox__media">
                <img
                    :src="{{ $galleryState }}[{{ $indexState }}].galleryImage"
                    :alt="{{ $galleryState }}[{{ $indexState }}].label"
                    class="pg-lightbox__image"
                >
            </div>

            <p class="pg-lightbox__caption" x-text="{{ $galleryState }}[{{ $indexState }}].label"></p>

        </div>

        <template x-if="{{ $galleryState }}.length > 1">
            <div>
                <button
                    type="button"
                    class="pg-lightbox__nav pg-lightbox__nav--prev"
                    aria-label="Previous image"
                    @click="{{ $prevAction }}"
                >
                    @icon('lightbox-chevron-left')
                </button>

                <button
                    type="button"
                    class="pg-lightbox__nav pg-lightbox__nav--next"
                    aria-label="Next image"
                    @click="{{ $nextAction }}"
                >
                    @icon('lightbox-chevron-right')
                </button>
            </div>
        </template>
    </div>
</div>

