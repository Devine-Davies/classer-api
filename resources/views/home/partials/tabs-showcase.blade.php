{{-- Tabs + Feature Showcase --}}

@php
    $tabs = [
        [
            'key' => 'explore',
            'label' => 'Explore',
            'title' => 'Navigate and explore years of footage',
            'imgSrc' => 'features/feature-3.png',
            'imgAlt' => 'A screenshot showcasing a video overview panel',
            'items' => [
                'See your footage as a visual library, not a list of folders',
                'Browse videos from all your connected hard drives',
                'Move through trips and recordings without opening every file'
            ],
        ],
        [
            'key' => 'maps',
            'label' => 'Maps',
            'title' => 'Track, save and remember places you been',
            'imgSrc' => 'features/feature-4.png',
            'imgAlt' => 'A screenshot showcasing a video overview panel',
            'items' => [
                'Use the map view to track places you visited',
                'Easily update video locations with our <br/> drag and drop feature',
            ],
        ],
        [
            'key' => 'key-moments',
            'label' => 'Key Moments',
            'title' => 'Save the moments worth coming back to',
            'imgSrc' => 'features/feature-1.png',
            'imgAlt' => 'A screenshot showcasing a capture of an action camera video',
            'items' => [
                'Mark the best parts of a video without changing the original',
                'Bring favourite moments from different recordings together',
                'Create collections around a trip, person or adventure',
            ],
        ],
        [
            'key' => 'telemetry',
            'label' => 'Telemetry',
            'title' => 'Discover more with telemetry',
            'imgSrc' => 'features/feature-2.png',
            'imgAlt' => 'A screenshot showcasing the speed of a mountain biker',
            'items' => [
                'Get insights through GPS, Maps, Speed',
                'Time track your runs, surf, rides and more',
                'Track places you been and search them on our map view',
            ],
        ],
    ];
@endphp

<style>
    /* Pill tab group with sliding indicator */
    .tabs-pills {
        position: relative;
        /* display: inline-flex; */
        background: #f2f2f2;
        border-radius: 999px;
        padding: 12px;
        gap: 0;
    }

    .tabs-pill {
        position: relative;
        z-index: 2;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #777;
        transition: color 0.4s ease;
        white-space: nowrap;
        padding: 8px 20px;
    }

    @media (min-width: 768px) {
        .tabs-pill {
            padding: 12px 28px;
        }
    }

    .tabs-pill.active {
        color: #fff;
    }

    .tabs-indicator {
        position: absolute;
        top: 6px;
        left: 6px;
        height: calc(100% - 12px);
        background-color: #0a404d;
        border-radius: 999px;
        transition:
            transform 0.45s cubic-bezier(.4, 0, .2, 1),
            width 0.45s cubic-bezier(.4, 0, .2, 1);
        z-index: 1;
        will-change: transform, width;
    }

    .tabs-panel {
        display: none;
        position: relative;
        flex-wrap: nowrap;
        margin: auto;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
    }

    .tabs-panel.active {
        display: flex;
        flex-direction: column;
    }

    @media (min-width: 768px) {
        .tabs-panel.active {
            display: flex;
            flex-direction: row-reverse;
            flex-wrap: nowrap;
        }
    }
</style>

<section class="w-full">
    {{-- Heading --}}
    <header class="text-center text-brand-color m-auto max-w-3xl">
        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-3 text-absolute not-italic font-medium leading-[108.54%] text-center">
            The place where your <span class="font-acent text-4xl md:text-5xl lg:text-6xl ">adventures</span> come back to life
        </h2>

        <p class="md:block lg:text-xl mt-4 mb-8">
            Classer app included
        </p>
    </header>

    {{-- Tabs --}}
    <section class="flex justify-center py-3 sticky top-0 z-10">
        <div class="tabs-pills overflow-x-auto max-w-full" role="tablist" data-tabs-pills>
            <span class="tabs-indicator" data-tabs-indicator></span>

            @foreach ($tabs as $i => $tab)
                <button
                    type="button"
                    role="tab"
                    data-tab-btn="{{ $tab['key'] }}"
                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                    @class([
                        'tabs-pill md:inline-flex',
                        'hidden' => $i > 2,
                        'active' => $i === 0,
                    ])>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </section>

    {{-- Panels --}}
    <section class="m-auto mt-6 md:mt-12 lg:my-22">
        @foreach ($tabs as $i => $tab)
            <article
                @class([
                    'tabs-panel ' => true,
                    'active' => $i === 0,
                    'md:flex-row-reverse' => $i % 2 === 0,
                    'md:flex-row' => $i % 2 !== 0,
                ])
                data-tab-panel="{{ $tab['key'] }}"
            >
                {{-- Image --}}
                <img
                    @class([
                        'hidden md:block absolute -right-[0] w-[60%] h-auto block transition-transform duration-500 ease-out',
                        'dm:translate-x-8',
                    ])
                    src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/welcome/' . $tab['imgSrc']) }}"
                    alt="{{ $tab['imgAlt'] }}"
                />

                {{-- Spacer for image --}}
                <div class="w-full md:w-1/2 overflow-visible">
                    <img
                        @class([
                            'md:opacity-0 w-full h-auto block transition-transform duration-500 ease-out',
                            'dm:translate-x-8',
                        ])
                        src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/welcome/' . $tab['imgSrc']) }}"
                        alt="{{ $tab['imgAlt'] }}"
                    />
                </div>

                {{-- Left Content --}}
                <div class="w-full md:w-1/2 place-self-center">
                    <div class="place-self-center pr-6 md:pr-12">
                        <h3 class="leading-tight text-brand-color text-xl md:text-2xl lg:text-3xl font-semibold text-center md:text-left">
                            {!! $tab['title'] !!}
                        </h3>

                        <div class="space-y-4 my-6">
                            @foreach ($tab['items'] as $item)
                                <div class="flex items-center justify-start gap-2">
                                    @icon(star)
                                    <p>{!! $item !!}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="hidden md:max-w-md lg:block w-[100%] rounded-2xl bg-[#ECF4EF] p-6">
                            <p class="mb-7 text-base text-[#0E561F]">
                                Includes 6 months of
                                <a href="#" class="underline underline-offset-2">
                                Classer Share
                                </a>
                                so you can privately share memories with family and friends.
                            </p>

                            <a href="{{ url('/app') }}" class="btn btn-lg uppercase">
                                Explore The App
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        <div class="lg:hidden w-[100%] rounded-2xl bg-[#ECF4EF] px-9 py-8 mt-3">
            <p class="mb-7 text-base leading-[1.3] text-[#0E561F]">
                Includes 6 months of
                <a href="#" class="underline underline-offset-2">
                Classer Share
                </a>
                so you can privately share memories with family and friends.
            </p>

            <div class="flex">
                <a href="{{ url('/app') }}" class="btn btn-lg uppercase">
                    Explore The App
                </a>
            </div>
        </div>
    </section>
</section>

<script>
(function () {
    var pillsContainer = document.querySelector('[data-tabs-pills]');
    if (!pillsContainer) return;

    var indicator = pillsContainer.querySelector('[data-tabs-indicator]');
    var buttons = Array.from(pillsContainer.querySelectorAll('[data-tab-btn]'));
    var panels = Array.from(document.querySelectorAll('[data-tab-panel]'));

    function moveIndicatorTo(btn) {
        if (!btn || !indicator) return;

        var parentRect = pillsContainer.getBoundingClientRect();
        var btnRect = btn.getBoundingClientRect();
        var offsetX = btnRect.left - parentRect.left + pillsContainer.scrollLeft;

        indicator.style.width = btnRect.width + 'px';
        indicator.style.transform = 'translateX(' + (offsetX - 6) + 'px)';
    }

    function activate(key, btn) {
        buttons.forEach(function (button) {
            var isActive = button === btn;

            button.classList.toggle('active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            panel.classList.toggle(
                'active',
                panel.getAttribute('data-tab-panel') === key
            );
        });

        moveIndicatorTo(btn);
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activate(btn.getAttribute('data-tab-btn'), btn);
        });
    });

    function init() {
        var active = pillsContainer.querySelector('.tabs-pill.active') || buttons[0];

        if (active) {
            activate(active.getAttribute('data-tab-btn'), active);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        requestAnimationFrame(init);
    }

    window.addEventListener('resize', function () {
        var active = pillsContainer.querySelector('.tabs-pill.active');
        moveIndicatorTo(active);
    });

    pillsContainer.addEventListener('scroll', function () {
        var active = pillsContainer.querySelector('.tabs-pill.active');
        moveIndicatorTo(active);
    });
})();
</script>