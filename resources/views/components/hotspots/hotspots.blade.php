@props([
    'hotspots' => [],
    'icon' => 'plus',
])

@once
    @vite('resources/views/components/hotspots/hotspots.css')
@endonce

<div
    {{ $attributes->class(['hotspots']) }}
>
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
            $iconClass = $hotspot['icon_class'] ?? '';

            $ariaLabel = $hotspot['aria_label'] ?? 'More information';
            $hotspotIcon = $hotspot['icon'] ?? $icon;
            $content = $hotspot['content'] ?? '';
        @endphp

        <div
            x-data="{ open: false }"
            class="hotspots__item"
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
                class="hotspots__trigger group {{ $buttonClass }}"
                @click="open = ! open"
                :aria-expanded="open.toString()"
                aria-label="{{ $ariaLabel }}"
            >
                <span class="hotspots__icon {{ $iconClass }}">
                    @icon($hotspotIcon)
                </span>
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition.opacity.scale.95
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                class="hotspots__panel {{ $panelClass }}"
            >
                {!! $content !!}
            </div>
        </div>
    @endforeach
</div>
