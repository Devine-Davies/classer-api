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
    class="fixed inset-0 z-[90] flex items-center justify-center p-4 sm:p-8"
    @click.self="{{ $closeAction }}"
    @keydown.escape.window="{{ $closeAction }}"
    @keydown.arrow-right.window="if ({{ $openState }}) {{ $nextAction }}"
    @keydown.arrow-left.window="if ({{ $openState }}) {{ $prevAction }}"
>
    <div class="absolute inset-0 bg-[#0a1e27]/45 backdrop-blur-md"></div>

    <div class="relative z-10 w-full max-w-6xl">
        <button
            type="button"
            class="fixed right-4 top-4 z-[95] p-1 text-white/85 transition hover:text-white"
            @click="{{ $closeAction }}"
            aria-label="Close lightbox"
        >
            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <div class="relative overflow-hidden rounded-3xl border border-white/20 bg-white/10 p-4 shadow-2xl backdrop-blur-xl sm:p-6">
            <div class="flex max-h-[calc(100vh-11rem)] min-h-[12rem] w-full items-center justify-center overflow-hidden rounded-2xl bg-black/20">
                <img
                    :src="{{ $galleryState }}[{{ $indexState }}].galleryImage"
                    :alt="{{ $galleryState }}[{{ $indexState }}].label"
                    class="max-h-[calc(100vh-13rem)] w-auto max-w-full object-contain"
                >
            </div>

            <p class="mt-3 text-center text-sm text-white/90" x-text="{{ $galleryState }}[{{ $indexState }}].label"></p>

        </div>

        <template x-if="{{ $galleryState }}.length > 1">
            <div>
                <button
                    type="button"
                    class="fixed left-3 top-1/2 z-[95] -translate-y-1/2 p-1 text-white/85 transition hover:text-white sm:left-5"
                    aria-label="Previous image"
                    @click="{{ $prevAction }}"
                >
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="fixed right-3 top-1/2 z-[95] -translate-y-1/2 p-1 text-white/85 transition hover:text-white sm:right-5"
                    aria-label="Next image"
                    @click="{{ $nextAction }}"
                >
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
