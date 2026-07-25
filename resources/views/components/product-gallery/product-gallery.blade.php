@props(['gallery' => []])

@once
    @vite('resources/views/components/product-gallery/product-gallery.css')
    @vite('resources/views/components/product-gallery/product-gallery.js')
@endonce

<div
    class="pg-gallery"
    x-data="{
        activeImage: 0,
        lightboxOpen: false,
        gallery: @js($gallery),
        next() {
            if (!this.gallery.length) {
                return;
            }

            this.activeImage = (this.activeImage + 1) % this.gallery.length;
        },
        prev() {
            if (!this.gallery.length) {
                return;
            }

            this.activeImage = (this.activeImage - 1 + this.gallery.length) % this.gallery.length;
        },
        openLightbox(index = null) {
            if (!this.gallery.length) {
                return;
            }

            if (index !== null) {
                this.activeImage = index;
            }

            this.lightboxOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
>
    <div class="pg-gallery__main-wrap">
        <div class="pg-gallery__main-frame">
            <div class="pg-gallery__main-aspect">
                @if (count($gallery) > 0)
                    <button
                        type="button"
                        class="pg-gallery__main-button"
                        @click="openLightbox()"
                        :aria-label="`Open ${gallery[activeImage].label} in lightbox`"
                    >
                        <img
                            :src="gallery[activeImage].galleryImage"
                            :alt="gallery[activeImage].label"
                            class="pg-gallery__main-image"
                        >
                    </button>
                @else
                    <div class="pg-gallery__empty">
                        No gallery images
                    </div>
                @endif
            </div>

            @if (count($gallery) > 1)
                <button
                    type="button"
                    aria-label="Previous image"
                    class="pg-gallery__preview-nav pg-gallery__preview-nav--prev"
                    @click="prev()"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <button
                    type="button"
                    aria-label="Next image"
                    class="pg-gallery__preview-nav pg-gallery__preview-nav--next"
                    @click="next()"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    @if (count($gallery) > 0)
        <div data-gallery-scroller class="pg-gallery__thumbs no-scrollbar">
            @foreach ($gallery as $index => $image)
                <button
                    type="button"
                    class="pg-gallery__thumb-button"
                    :class="activeImage === {{ $index }}
                        ? 'pg-gallery__thumb-button--active'
                        : 'pg-gallery__thumb-button--inactive'"
                    @click="activeImage = {{ $index }}"
                    @dblclick="openLightbox({{ $index }})"
                    aria-label="{{ $image['aria'] }}"
                >
                    <img
                        src="{{ $image['thumbnail'] }}"
                        alt="{{ $image['label'] }} thumbnail"
                        class="pg-gallery__thumb-image"
                        draggable="false"
                    >
                </button>
            @endforeach
        </div>
    @endif

    <x-gallery-lightbox.gallery-lightbox />
</div>

@once
    <script src="https://unpkg.com/alpinejs" defer></script>
@endonce
