{{-- Tabs Showcase: The place where your adventures come back to life --}}
@php
    $tabs = [
        [
            'key'   => 'explore',
            'label' => 'Explore',
            'title' => 'Discover more through exploring',
            'desc'  => 'Browse your library by trip, location or activity. Everything you captured is one click away.',
            'image' => 'https://placeholders.io/700/500',
            'alt'   => 'Explore view',
        ],
        [
            'key'   => 'key-moments',
            'label' => 'Key Moments',
            'title' => 'Never lose a key moment again',
            'desc'  => 'Classer Home surfaces the best clips automatically — the jumps, the crashes, the smiles.',
            'image' => 'https://placeholders.io/700/500',
            'alt'   => 'Key moments view',
        ],
        [
            'key'   => 'maps',
            'label' => 'Maps',
            'title' => 'See where your adventures happened',
            'desc'  => 'Every clip placed on the map. Relive a ride or a trip exactly where it unfolded.',
            'image' => 'https://placeholders.io/700/500',
            'alt'   => 'Maps view',
        ],
        [
            'key'   => 'telemetry',
            'label' => 'Telemetry',
            'title' => 'Discover more through telemetry data',
            'desc'  => 'View speed, GPS and movement data from your recordings. Combine maps, framing and performance insights together. Track rides, runs, and sessions and adventures over time.',
            'image' => 'https://placeholders.io/700/500',
            'alt'   => 'Telemetry view',
        ],
    ];
@endphp

<style>
    /* Pill tab group with sliding indicator */
    .tabs-pills {
        position: relative;
        display: inline-flex;
        background: #f5f5f5;
        border-radius: 999px;
        padding: 6px;
        gap: 0;
    }

    .tabs-pill {
        position: relative;
        z-index: 2;
        padding: 12px 28px;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #777;
        transition: color 0.4s ease;
        white-space: nowrap;
    }

    .tabs-pill.active { color: #fff; }

    .tabs-indicator {
        position: absolute;
        top: 6px;
        left: 6px;
        height: calc(100% - 12px);
        background-color: #0a404d;
        border-radius: 999px;
        transition: transform 0.45s cubic-bezier(.4,0,.2,1),
                    width 0.45s cubic-bezier(.4,0,.2,1);
        z-index: 1;
        will-change: transform, width;
    }

    /* Panel transitions handled by the rules at the bottom of this file. */
</style>

<div class="mx-auto w-full max-w-6xl px-6 md:px-8">

    {{-- Heading --}}
    <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight text-center mb-10 md:mb-14 text-brand-color">
        The place where your adventures<br>
        come back to life
    </h2>

    {{-- Tabs --}}
    <div class="flex justify-center mb-12 md:mb-16">
        <div class="tabs-pills overflow-x-auto" role="tablist" data-tabs-pills>
            <span class="tabs-indicator" data-tabs-indicator></span>
            @foreach ($tabs as $i => $tab)
                <button
                    type="button"
                    role="tab"
                    class="tabs-pill {{ $i === count($tabs) - 1 ? 'active' : '' }}"
                    data-tab-btn="{{ $tab['key'] }}"
                    aria-selected="{{ $i === count($tabs) - 1 ? 'true' : 'false' }}"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Panels --}}
    <div class="relative container m-auto max-w-7xl">
        @foreach ($tabs as $i => $tab)
            <article
                class="tabs-panel md:flex md:flex-nowrap md:flex-row-reverse m-auto items-center gap-8 md:gap-12 {{ $i === count($tabs) - 1 ? 'active' : '' }}"
                data-tab-panel="{{ $tab['key'] }}"
            >
                {{-- Image --}}
                <div class="w-full md:w-1/2 overflow-hidden">
                    <img
                        class="w-full h-auto block rounded-2xl"
                        src="{{ $tab['image'] }}"
                        alt="{{ $tab['alt'] }}"
                    />
                </div>

                {{-- Copy --}}
                <div class="w-full md:w-1/2 place-self-center">
                    <div class="px-6 md:px-8">
                        <h3 class="leading-tight my-6 lg:mt-0 text-brand-color text-xl md:text-2xl lg:text-4xl font-semibold text-center md:text-left">
                            {{ $tab['title'] }}
                        </h3>
                        @foreach (array_filter(array_map('trim', explode('. ', rtrim($tab['desc'], '.')))) as $item)
                            <div class="mb-4 flex justify-start gap-2 items-center">
                                <span class="w-6 h-6">
                                    @icon(star)
                                </span>
                                <p>{{ $item }}.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>

<style>
    /* Panel needs to switch from `display: none` to `display: flex` for the
       features.blade.php layout to work, overriding the earlier `grid` rule. */
    .tabs-panel { display: none; }
    .tabs-panel.active { display: flex; flex-direction: column; }
    @media (min-width: 768px) {
        .tabs-panel.active { display: flex; flex-direction: row-reverse; flex-wrap: nowrap; }
    }
</style>

<script>
(function () {
    var pillsContainer = document.querySelector('[data-tabs-pills]');
    if (!pillsContainer) return;

    var indicator = pillsContainer.querySelector('[data-tabs-indicator]');
    var buttons   = Array.from(pillsContainer.querySelectorAll('[data-tab-btn]'));
    var panels    = Array.from(document.querySelectorAll('[data-tab-panel]'));

    function moveIndicatorTo(btn) {
        if (!btn || !indicator) return;
        var parentRect = pillsContainer.getBoundingClientRect();
        var btnRect    = btn.getBoundingClientRect();
        var offsetX    = btnRect.left - parentRect.left;
        indicator.style.width     = btnRect.width + 'px';
        indicator.style.transform = 'translateX(' + (offsetX - 6) + 'px)';
    }

    function activate(key, btn) {
        buttons.forEach(function (b) {
            var isActive = b === btn;
            b.classList.toggle('active', isActive);
            b.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            p.classList.toggle('active', p.getAttribute('data-tab-panel') === key);
        });
        moveIndicatorTo(btn);
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activate(btn.getAttribute('data-tab-btn'), btn);
        });
    });

    // Initial position — pick the currently active button
    function init() {
        var active = pillsContainer.querySelector('.tabs-pill.active') || buttons[0];
        moveIndicatorTo(active);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // Defer to next frame so layout is settled
        requestAnimationFrame(init);
    }

    window.addEventListener('resize', function () {
        var active = pillsContainer.querySelector('.tabs-pill.active');
        moveIndicatorTo(active);
    });
})();
</script>
