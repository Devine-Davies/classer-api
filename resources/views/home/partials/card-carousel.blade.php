{{-- resources/views/components/card-carousel.blade.php --}}

@php
    $cards = collect([
        [
            'image' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?auto=format&fit=crop&w=800&q=80',
            'title' => 'Mountain biking',
            'quote' => 'I’m afraid to die, but I’m more afraid to not live.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80',
            'title' => 'Swim adventures',
            'quote' => 'Go at your own pace and do what you can.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=800&q=80',
            'title' => 'Family memories',
            'quote' => 'There’s nothing more rewarding than pushing through it.',
        ],
        [
            'image' => 'https://images.uncard-carouselsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=800&q=80',
            'title' => 'Outdoor living',
            'quote' => 'Movement changes how you see the world.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80',
            'title' => 'Climbing days',
            'quote' => 'The view is worth the effort.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=800&q=80',
            'title' => 'Lake escapes',
            'quote' => 'Still water, clear mind.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=800&q=80',
            'title' => 'Forest walks',
            'quote' => 'Slow down and notice more.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1476611338391-6f395a0dd82f?auto=format&fit=crop&w=800&q=80',
            'title' => 'Road trips',
            'quote' => 'The long way often teaches the most.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=800&q=80',
            'title' => 'Night trails',
            'quote' => 'Some paths only make sense in motion.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=800&q=80',
            'title' => 'Weekend hikes',
            'quote' => 'The path appears by walking it.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
            'title' => 'River routes',
            'quote' => 'Follow the current, but keep your direction.',
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80',
            'title' => 'Open country',
            'quote' => 'Wide spaces make room for new thoughts.',
        ],
    ])->map(fn ($card) => (object) $card);
@endphp

<section class="w-full max-w-full overflow-hidden py-8">
    <div class="mx-auto max-w-screen-md text-center mb-6 md:mb-12 px-4">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-brand-color mb-3 text-absolute font-['Hanken_Grotesk'] not-italic font-medium leading-[108.54%] text-center">
            The adventures behind the footage
        </h2>

        <p class="text-base text-slate-600">
            The adventures behind the footage.
        </p>
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
                            src="{{ $card->image }}"
                            alt="{{ $card->title }}"
                            class="pointer-events-none h-full w-full object-cover"
                            loading="lazy"
                            draggable="false"
                        >
                    </div>

                    <div class="mt-3">
                        <h3 class="text-2xl font-semibold leading-tight text-slate-900 text-brand-color">
                            {{ $card->title }}
                        </h3>

                        <p class="mt-1 text-sm italic leading-snug text-slate-500">
                            “{{ $card->quote }}”
                        </p>
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
                if (event.button !== 0) return;

                stopMomentum();

                isDragging = true;
                hasDragged = false;

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

                if (Math.abs(dragDistance) > 3) {
                    hasDragged = true;
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
                if (!hasDragged) return;

                event.preventDefault();
                event.stopPropagation();

                hasDragged = false;
            }, true);
        });
    });
</script>