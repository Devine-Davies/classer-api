{{-- Showcase 2 — Quiet · Effortless · Private --}}
@php
    $slides = [
        [
            'key'   => 'quiet',
            'label' => 'Quiet',
            'image' => asset('/assets/images/classer-home/showcase-2/01.jpg'),
            'alt'   => 'Classer Home sitting quietly in a living room',
        ],
        [
            'key'   => 'effortless',
            'label' => 'Effortless',
            'image' => asset('/assets/images/classer-home/showcase-2/02.jpg'),
            'alt'   => 'Classer Home effortlessly organising footage',
        ],
        [
            'key'   => 'private',
            'label' => 'Private',
            'image' => asset('/assets/images/classer-home/showcase-2/03.jpg'),
            'alt'   => 'Classer Home keeping your data private at home',
        ],
    ];
@endphp

<style>
    /* ── Pill button group ── */
    .showcase2-pills {
        display: inline-flex;
        background: #f5f5f5;
        border-radius: 999px;
        padding: 6px;
        gap: 4px;
    }

    .showcase2-pill {
        padding: 14px 36px;
        border-radius: 999px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #999;
        transition: background-color 0.4s ease, color 0.4s ease;
    }

    .showcase2-pill.active {
        background-color: #0a404d;
        color: #fff;
    }

    /* ── Slide viewport ── */
    .showcase2-viewport {
        position: relative;
        overflow: hidden;
        width: 100vw;
        left: 50%;
        transform: translateX(-50%);
    }

    .showcase2-track {
        display: flex;
        transition: transform 0.8s cubic-bezier(.4, 0, .2, 1);
        will-change: transform;
    }

    .showcase2-slide {
        flex: 0 0 100vw;
        width: 100vw;
    }

    .showcase2-slide img {
        width: 100%;
        height: auto;
        display: block;
    }

</style>

<div class="mx-auto max-w-7xl px-8">
    {{-- Pill buttons --}}
    <div class="flex justify-center mb-10 md:mb-14">
        <div class="showcase2-pills">
            @foreach ($slides as $i => $slide)
                <button
                    type="button"
                    class="showcase2-pill {{ $i === 0 ? 'active' : '' }}"
                    data-showcase2-btn="{{ $i }}"
                    aria-label="{{ $slide['label'] }}"
                >
                    {{ $slide['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Image carousel --}}
    <div class="showcase2-viewport">
        <div class="showcase2-track" id="showcase2-track">
            @foreach ($slides as $slide)
                <div class="showcase2-slide">
                    <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}">
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
(function () {
    var track   = document.getElementById('showcase2-track');
    var buttons = Array.from(document.querySelectorAll('[data-showcase2-btn]'));
    var current = 0;

    function goTo(index) {
        buttons[current].classList.remove('active');
        current = index;
        buttons[current].classList.add('active');
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
    }

    buttons.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            goTo(i);
        });
    });
})();
</script>
