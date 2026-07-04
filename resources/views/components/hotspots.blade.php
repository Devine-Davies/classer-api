@props([
    'hotspots' => [],
    'icon' => 'plus',
])

<div {{ $attributes->merge(['class' => 'absolute inset-0 z-50 pointer-events-none']) }}>
    @foreach ($hotspots as $hotspot)
        @php
            $baseTop = $hotspot['position']['base']['top'] ?? '50%';
            $baseLeft = $hotspot['position']['base']['left'] ?? '50%';

            $mdTop = $hotspot['position']['md']['top'] ?? $baseTop;
            $mdLeft = $hotspot['position']['md']['left'] ?? $baseLeft;

            $lgTop = $hotspot['position']['lg']['top'] ?? $mdTop;
            $lgLeft = $hotspot['position']['lg']['left'] ?? $mdLeft;

            $buttonClass = $hotspot['button_class'] ?? '';
            $panelClass = $hotspot['panel_class'] ?? '';
            $ariaLabel = $hotspot['aria_label'] ?? 'More information';
        @endphp

        <div
            x-data="{ open: false }"
            class="
                absolute z-50 -translate-x-1/2 -translate-y-1/2 pointer-events-auto
                top-[var(--hotspot-top)] left-[var(--hotspot-left)]
                md:top-[var(--hotspot-top-md)] md:left-[var(--hotspot-left-md)]
                lg:top-[var(--hotspot-top-lg)] lg:left-[var(--hotspot-left-lg)]
            "
            style="
                --hotspot-top: {{ $baseTop }};
                --hotspot-left: {{ $baseLeft }};
                --hotspot-top-md: {{ $mdTop }};
                --hotspot-left-md: {{ $mdLeft }};
                --hotspot-top-lg: {{ $lgTop }};
                --hotspot-left-lg: {{ $lgLeft }};
            "
        >
            <button
                type="button"
                class="aspect-square p-1 rounded-full bg-white flex items-center text-black/80 hover:text-black/90 transition {{ $buttonClass }} group animate-hotspot-idle"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-label="{{ $ariaLabel }}"
            >
                <span class="inline-flex transition duration-300 ease-out transform-gpu group-hover:scale-110 group-hover:rotate-180 cursor-pointer">
                    @icon($hotspot['icon'] ?? $icon, $hotspot['icon_class'] ?? 'w-3 h-5')
                </span>
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition
                @click.outside="open = false"
                class="absolute left-1/2 bottom-full z-50 mb-3 w-80 -translate-x-1/2 rounded-xl border border-white/10 bg-black/90 p-4 text-sm text-white shadow-2xl shadow-black/40 {{ $panelClass }}"
            >
                {!! $hotspot['content'] ?? '' !!}
            </div>
        </div>
    @endforeach
</div>

<style>
    @keyframes hotspotIdlePulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.28);
        }

        50% {
            transform: scale(1.035);
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
        }
    }

    .animate-hotspot-idle {
        animation: hotspotIdlePulse 2.8s ease-in-out infinite;
        transform-origin: center;
        will-change: transform, box-shadow;
    }

    .animate-hotspot-idle:hover,
    .animate-hotspot-idle:focus-visible {
        animation-play-state: paused;
    }
</style>