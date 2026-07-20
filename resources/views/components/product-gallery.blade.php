@props(['gallery' => []])

<div
    class="min-w-0"
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
    <div class="w-full">
        <div class="relative overflow-hidden rounded-2xl bg-[#f3ead9]">
            <div class="aspect-square w-full">
                @if (count($gallery) > 0)
                    <button
                        type="button"
                        class="h-full w-full"
                        @click="openLightbox()"
                        :aria-label="`Open ${gallery[activeImage].label} in lightbox`"
                    >
                        <img
                            :src="gallery[activeImage].galleryImage"
                            :alt="gallery[activeImage].label"
                            class="h-full w-full object-contain"
                        >
                    </button>
                @else
                    <div class="flex h-full w-full items-center justify-center text-sm text-[#51727a]">
                        No gallery images
                    </div>
                @endif
            </div>

            @if (count($gallery) > 1)
                <button
                    type="button"
                    aria-label="Previous image"
                    class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-[#0d4150] shadow-sm backdrop-blur transition hover:bg-white"
                    @click="prev()"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <button
                    type="button"
                    aria-label="Next image"
                    class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-[#0d4150] shadow-sm backdrop-blur transition hover:bg-white"
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
        <div data-gallery-scroller class="no-scrollbar mt-4 flex min-h-[7rem] gap-3 overflow-x-auto pb-2 sm:min-h-[8rem]">
            @foreach ($gallery as $index => $image)
                <button
                    type="button"
                    class="h-20 w-24 shrink-0 overflow-hidden rounded-xl border bg-[#f7f7f4] transition select-none sm:h-24 sm:w-28"
                    :class="activeImage === {{ $index }}
                        ? 'border-[#0d4150] ring-1 ring-[#0d4150]'
                        : 'border-transparent hover:border-[#0d4150]/30'"
                    @click="activeImage = {{ $index }}"
                    @dblclick="openLightbox({{ $index }})"
                    aria-label="{{ $image['aria'] }}"
                >
                    <img
                        src="{{ $image['thumbnail'] }}"
                        alt="{{ $image['label'] }} thumbnail"
                        class="h-full w-full object-cover select-none"
                        draggable="false"
                    >
                </button>
            @endforeach
        </div>
    @endif

    <x-product-gallery-lightbox />
</div>

@once
    <script src="https://unpkg.com/alpinejs" defer></script>

    <script>
        (() => {
            const DRAG_THRESHOLD_PX = 5;
            const PRESS_TO_DRAG_MS = 140;
            const LONG_PRESS_MIN_DELTA_PX = 2;
            const scrollers = document.querySelectorAll('[data-gallery-scroller]');

            scrollers.forEach((scroller) => {
                if (scroller.dataset.galleryScrollerReady === 'true') {
                    return;
                }

                scroller.dataset.galleryScrollerReady = 'true';

                let isPointerDown = false;
                let isDragging = false;
                let suppressNextClick = false;
                let startX = 0;
                let startScrollLeft = 0;
                let pressStartedAt = 0;

                scroller.style.cursor = 'grab';
                scroller.style.touchAction = 'pan-y';

                scroller.addEventListener('pointerdown', (event) => {
                    if (event.button !== 0) {
                        return;
                    }

                    isPointerDown = true;
                    isDragging = false;
                    startX = event.clientX;
                    startScrollLeft = scroller.scrollLeft;
                    pressStartedAt = Date.now();
                    scroller.style.cursor = 'grabbing';
                });

                const onPointerMove = (event) => {
                    if (!isPointerDown) {
                        return;
                    }

                    const deltaX = event.clientX - startX;
                    const elapsedMs = Date.now() - pressStartedAt;
                    const movedEnough = Math.abs(deltaX) > DRAG_THRESHOLD_PX;
                    const longPressIntent = elapsedMs > PRESS_TO_DRAG_MS && Math.abs(deltaX) > LONG_PRESS_MIN_DELTA_PX;

                    if (movedEnough || longPressIntent) {
                        isDragging = true;
                        scroller.dataset.dragging = 'true';
                    }

                    if (isDragging) {
                        event.preventDefault();
                        scroller.scrollLeft = startScrollLeft - deltaX;
                    }
                };

                const stopPointerInteraction = () => {
                    if (!isPointerDown) {
                        return;
                    }

                    isPointerDown = false;
                    scroller.style.cursor = 'grab';

                    if (isDragging) {
                        suppressNextClick = true;
                    }

                    isDragging = false;
                    delete scroller.dataset.dragging;
                };

                scroller.addEventListener('pointermove', onPointerMove);
                window.addEventListener('pointerup', stopPointerInteraction);
                window.addEventListener('pointercancel', stopPointerInteraction);

                scroller.addEventListener('click', (event) => {
                    if (suppressNextClick) {
                        event.preventDefault();
                        event.stopPropagation();
                        suppressNextClick = false;
                    }
                }, true);
            });
        })();
    </script>
@endonce
