@props([
    'cards' => [],
    'title' => 'See our community stories',
    'intro' => 'The adventures behind the footage.',
])

@php
    $cards = collect($cards)->map(fn ($card) => (object) $card);
@endphp

<section {{ $attributes->merge(['class' => 'w-full max-w-full overflow-hidden py-8']) }}>
    <div class="mx-auto max-w-screen-md text-center mb-6 md:mb-12 px-4">
        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-3 text-absolute font-medium leading-[108.54%] text-center">
            {{ $title }}
        </h2>

        @if ($intro)
            <p class="text-base text-slate-600">
                {{ $intro }}
            </p>
        @endif
    </div>

    <div class="w-full max-w-full">
        <div
            data-drag-scroll
            class="card-scroll flex w-full max-w-full cursor-grab select-none gap-4 overflow-x-auto overflow-y-hidden px-6 pb-4 sm:px-10 lg:px-16"
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


<style>
    .card-scroll {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 23, 42, 0.18) transparent;
    }

    .card-scroll::-webkit-scrollbar {
        height: 7px;
    }

    .card-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .card-scroll::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.16);
        border-radius: 999px;
    }

    .card-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(15, 23, 42, 0.28);
    }

    .card-scroll.is-dragging {
        cursor: grabbing;
        user-select: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliders = document.querySelectorAll('[data-drag-scroll]');

        sliders.forEach((slider) => {
            let isDragging = false;
            let startX = 0;
            let startScrollLeft = 0;
            let lastX = 0;
            let lastTime = 0;
            let velocity = 0;
            let momentumFrame = null;
            let hasDragged = false;
            let shouldCancelClick = false;

            const stopMomentum = () => {
                if (momentumFrame) {
                    cancelAnimationFrame(momentumFrame);
                    momentumFrame = null;
                }
            };

            const runMomentum = () => {
                const friction = 0.92;
                const minVelocity = 0.08;

                if (Math.abs(velocity) < minVelocity) {
                    momentumFrame = null;
                    return;
                }

                slider.scrollLeft -= velocity;
                velocity *= friction;

                const atStart = slider.scrollLeft <= 0;
                const atEnd = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 1;

                if ((atStart && velocity > 0) || (atEnd && velocity < 0)) {
                    momentumFrame = null;
                    return;
                }

                momentumFrame = requestAnimationFrame(runMomentum);
            };

            slider.addEventListener('pointerdown', (event) => {
                const interactiveElement = event.target.closest('a, button, input, textarea, select, label');
                if (interactiveElement) return;

                if (event.button !== 0) return;

                stopMomentum();

                isDragging = true;
                hasDragged = false;
                shouldCancelClick = false;

                startX = event.clientX;
                startScrollLeft = slider.scrollLeft;
                lastX = event.clientX;
                lastTime = performance.now();
                velocity = 0;

                slider.classList.add('is-dragging');
                slider.setPointerCapture(event.pointerId);
            });

            slider.addEventListener('pointermove', (event) => {
                if (!isDragging) return;

                const now = performance.now();
                const currentX = event.clientX;
                const dragDistance = currentX - startX;

                const deltaX = currentX - lastX;
                const deltaTime = Math.max(now - lastTime, 1);

                if (Math.abs(dragDistance) > 6) {
                    hasDragged = true;
                    shouldCancelClick = true;
                }

                slider.scrollLeft = startScrollLeft - dragDistance;

                /**
                 * Smooth velocity tracking.
                 * This avoids jumps or pauses when the pointer is released.
                 */
                const instantVelocity = deltaX / deltaTime * 16.67;
                velocity = velocity * 0.8 + instantVelocity * 0.2;

                lastX = currentX;
                lastTime = now;

                event.preventDefault();
            });

            const endDrag = (event) => {
                if (!isDragging) return;

                isDragging = false;
                slider.classList.remove('is-dragging');

                try {
                    slider.releasePointerCapture(event.pointerId);
                } catch (error) {}

                /**
                 * Start momentum immediately in the same release flow.
                 * No timeout, no smooth-scroll handoff.
                 */
                if (Math.abs(velocity) > 0.08) {
                    stopMomentum();
                    momentumFrame = requestAnimationFrame(runMomentum);
                }
            };

            slider.addEventListener('pointerup', endDrag);
            slider.addEventListener('pointercancel', endDrag);
            slider.addEventListener('lostpointercapture', endDrag);

            slider.addEventListener('click', (event) => {
                if (!shouldCancelClick) return;

                event.preventDefault();
                event.stopPropagation();

                shouldCancelClick = false;
            }, true);
        });
    });
</script>