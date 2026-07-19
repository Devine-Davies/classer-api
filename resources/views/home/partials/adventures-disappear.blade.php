@php
    $cards = [
        [
            'label' => 'The old way',
            'labelClass' => 'text-[#a2a2a2] font-semibold',
            'icon' => 'close',
            'iconClass' => 'border-[#9b9b9b] text-[#9b9b9b]',
            'title' => 'Record everything.<br>Find <span class="font-semibold">nothing</span>.',
            'description' => 'Thousands of videos are recorded every year, but most are never seen again, disappearing into folders and hard drives.',
            'cardClass' => "bg-[#fafafa]",
            'titleClass' => 'max-w-[310px]',
            'descriptionClass' => 'max-w-[330px]',
            'showSVG' => 1,
        ],
        [
            'label' => 'With Classer',
            'labelClass' => 'text-[#008b29] font-bold',
            'icon' => 'tick',
            'iconClass' => 'border-[#009b2f] text-[#009b2f]',
            'title' => 'Record everything.<br>Find <span class="font-semibold">anything</span>.',
            'description' => 'Classer turns your existing hard drives into a visual library that unlock your forgotten footage.',
            'cardClass' => "bg-[#F6F4F1]",
            'titleClass' => 'max-w-[310px]',
            'descriptionClass' => 'max-w-[300px]',
            'showSVG' => 2,
        ],
    ];
@endphp

<section class="w-full">
    <header class="mb-12">
        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-3 text-absolute not-italic font-medium leading-[108.54%] text-center">
            Most <span class="font-acent font-bold text-4xl md:text-5xl lg:text-6xl">adventures</span> disappear <br> into hard drives
        </h2>
        <p class="text-base text-slate-600 max-w-sm md:max-w-2xl mx-auto text-center mt-3">
            Thousands of videos are recorded every year, but most are never seen again.
        </p>
    </header>

    <div
        data-adventures-scroll
        class="
            adventures-scroll relative mx-auto
            flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth px-4 pb-5
            touch-pan-x overscroll-x-contain cursor-grab select-none
            md:grid md:max-w-7xl md:grid-cols-2 md:gap-5 md:overflow-visible md:px-0 md:pb-0 md:cursor-auto md:select-auto
        "
    >
        <div
            class="
                flex absolute left-[83%] md:left-1/2 top-[20%] z-20 h-24 w-24
                -translate-x-1/2 items-center justify-center rounded-full
                bg-black/10 text-3xl font-bold text-[#8c8c8c]
            "
        >
            VS
        </div>

        @foreach ($cards as $card)
            <div
                data-adventure-card
                class="
                    relative w-[70vw] shrink-0 snap-center overflow-hidden rounded-2xl
                    min-h-[560px] md:min-h-[580px] lg:min-h-[600px]
                    bg-no-repeat p-6
                    md:w-auto md:shrink md:snap-none md:p-9
                    {{ $card['cardClass'] }}
                "
            >
                <div class="relative z-10 flex items-start gap-3 md:gap-4">
                    <div class="h-6 w-6 flex justify-center items-center rounded-full border-2 {{ $card['iconClass'] }}">
                        <div class="h-4 w-4">
                            @icon($card['icon'])
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 text-left">
                        <span class="text-xs uppercase tracking-wide {{ $card['labelClass'] }}">
                            {{ $card['label'] }}
                        </span>

                        <h2 class="{{ $card['titleClass'] }} text-2xl md:text-3xl leading-tight tracking-[-0.02em] text-[#202020]">
                            {!! $card['title'] !!}
                        </h2>

                        <p class="mt-5 {{ $card['descriptionClass'] }} text-base md:text-base leading-relaxed text-[#a4a4a4]">
                            {{ $card['description'] }}
                        </p>
                    </div>
                </div>

                <div class="absolute bottom-0 -left-[25%] md:-left-[10%] lg:left-[0%] z-0 ">
                    @if ($card['showSVG'] === 1)
                        @include('home.partials.images.adventures-disappear-svg-1')
                    @elseif ($card['showSVG'] === 2)
                        @include('home.partials.images.adventures-disappear-svg-2')
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

<style>
    .adventures-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
        /* -webkit-overflow-scrolling: touch; */
    }

    .adventures-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .adventures-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .adventures-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.25);
        border-radius: 999px;
    }

    .adventures-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.4);
    }

    .adventures-scroll.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    .adventures-scroll.is-settling {
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    @media (min-width: 768px) {
        .adventures-scroll {
            scrollbar-width: auto;
        }

        .adventures-scroll::-webkit-scrollbar {
            display: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliders = document.querySelectorAll('[data-adventures-scroll]');

        sliders.forEach((slider) => {
            let isDragging = false;
            let isSettling = false;

            let startX = 0;
            let lastX = 0;
            let scrollLeft = 0;
            let velocity = 0;
            let lastMoveTime = 0;
            let hasMoved = false;
            let pointerId = null;
            let rafId = null;

            const dragSensitivity = 1.35;
            const dragThreshold = 4;
            const friction = 0.92;
            const minVelocity = 0.35;
            const momentumMultiplier = 18;

            const isMobileLayout = () => window.innerWidth < 768;

            const getCards = () => {
                return Array.from(slider.querySelectorAll('[data-adventure-card]'));
            };

            const snapToClosestCard = () => {
                if (!isMobileLayout()) return;

                const cards = getCards();
                if (!cards.length) return;

                const sliderRect = slider.getBoundingClientRect();
                const sliderCenter = sliderRect.left + sliderRect.width / 2;

                let closestCard = cards[0];
                let closestDistance = Infinity;

                cards.forEach((card) => {
                    const cardRect = card.getBoundingClientRect();
                    const cardCenter = cardRect.left + cardRect.width / 2;
                    const distance = Math.abs(sliderCenter - cardCenter);

                    if (distance < closestDistance) {
                        closestDistance = distance;
                        closestCard = card;
                    }
                });

                const targetScrollLeft =
                    slider.scrollLeft +
                    closestCard.getBoundingClientRect().left -
                    sliderRect.left -
                    (slider.clientWidth - closestCard.clientWidth) / 2;

                slider.classList.remove('is-settling');

                slider.scrollTo({
                    left: targetScrollLeft,
                    behavior: 'smooth',
                });
            };

            const stopMomentum = () => {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                }

                isSettling = false;
                slider.classList.remove('is-settling');
            };

            const applyMomentum = () => {
                if (!isMobileLayout()) {
                    stopMomentum();
                    return;
                }

                isSettling = true;
                slider.classList.add('is-settling');

                const step = () => {
                    velocity *= friction;

                    slider.scrollLeft -= velocity * momentumMultiplier;

                    if (Math.abs(velocity) < minVelocity) {
                        stopMomentum();
                        snapToClosestCard();
                        return;
                    }

                    rafId = requestAnimationFrame(step);
                };

                rafId = requestAnimationFrame(step);
            };

            const startDragging = (event) => {
                if (!isMobileLayout()) return;

                stopMomentum();

                isDragging = true;
                hasMoved = false;
                pointerId = event.pointerId;

                slider.classList.add('is-dragging');

                if (slider.setPointerCapture) {
                    slider.setPointerCapture(event.pointerId);
                }

                startX = event.clientX;
                lastX = event.clientX;
                scrollLeft = slider.scrollLeft;
                velocity = 0;
                lastMoveTime = performance.now();
            };

            const drag = (event) => {
                if (!isDragging || !isMobileLayout()) return;

                event.preventDefault();

                const currentX = event.clientX;
                const now = performance.now();

                const deltaX = currentX - startX;
                const frameDeltaX = currentX - lastX;
                const frameTime = Math.max(now - lastMoveTime, 16);

                velocity = frameDeltaX / frameTime;

                if (Math.abs(deltaX) > dragThreshold) {
                    hasMoved = true;
                }

                slider.scrollLeft = scrollLeft - deltaX * dragSensitivity;

                lastX = currentX;
                lastMoveTime = now;
            };

            const stopDragging = (event) => {
                if (!isDragging) return;

                isDragging = false;
                slider.classList.remove('is-dragging');

                if (
                    pointerId !== null &&
                    slider.hasPointerCapture &&
                    slider.hasPointerCapture(pointerId)
                ) {
                    slider.releasePointerCapture(pointerId);
                }

                pointerId = null;

                if (Math.abs(velocity) > minVelocity) {
                    applyMomentum();
                } else {
                    snapToClosestCard();
                }

                window.setTimeout(() => {
                    hasMoved = false;
                }, 200);
            };

            slider.addEventListener('pointerdown', startDragging, { passive: false });
            slider.addEventListener('pointermove', drag, { passive: false });
            slider.addEventListener('pointerup', stopDragging);
            slider.addEventListener('pointercancel', stopDragging);

            slider.addEventListener('click', (event) => {
                if (!hasMoved) return;

                event.preventDefault();
                event.stopPropagation();
            });

            window.addEventListener('resize', () => {
                if (!isMobileLayout()) {
                    stopMomentum();
                    slider.classList.remove('is-dragging');
                    isDragging = false;
                    hasMoved = false;
                    pointerId = null;
                }
            });
        });
    });
</script>