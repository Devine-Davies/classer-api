@props([
    'steps' => [],
])

@once
    <script>
        window.classerStepReveal = () => ({
            isVisible: false,
            hasMounted: false,
            observer: null,
            init() {
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (prefersReducedMotion || !('IntersectionObserver' in window)) {
                    this.isVisible = true;
                    this.hasMounted = true;
                    return;
                }

                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        this.isVisible = true;
                        this.observer?.unobserve(this.$el);
                    });
                }, {
                    threshold: 0.25,
                    rootMargin: '0px 0px -10% 0px',
                });

                this.observer.observe(this.$el);
                this.hasMounted = true;
            },
        });
    </script>
@endonce

@foreach ($steps as $step)
    @php
        $imageRight = ($step['imagePosition'] ?? 'left') === 'right';
    @endphp

    <artical
        x-data="classerStepReveal()"
        x-init="init()"
        :class="hasMounted && !isVisible ? 'translate-y-8 opacity-0' : 'translate-y-0 opacity-100'"
        class="mx-auto mt-12 flex max-w-7xl flex-col items-center space-y-6 transition-all duration-700 ease-out will-change-transform md:space-y-10 lg:space-y-14 {{ $imageRight ? 'md:flex-row-reverse' : 'md:flex-row' }}"
    >
        {{-- Image --}}
        <div class="w-full md:w-[65%] flex-shrink-0">
            <div class="overflow-hidden rounded-2xl">
                <img
                    src="{{ $step['image'] }}"
                    alt="{{ $step['alt'] ?? '' }}"
                    class="w-full h-auto object-cover"
                />
            </div>
        </div>

        {{-- Text --}}
        <div class="w-full md:w-[35%] {{ $imageRight ? 'md:pr-10 lg:pr-16' : 'md:pl-10 lg:pl-16' }} flex flex-col justify-center space-y-2 md:space-y-3">
            <h2 class="leading-tight text-brand-color text-xl md:text-2xl lg:text-3xl font-semibold text-center md:text-left" >
                {{ $step['label'] }}
            </h2>
            <p class="text-gray-500 text-base md:text-lg leading-relaxed">
                {{ $step['text'] }}
            </p>
        </div>
    </artical>
@endforeach
