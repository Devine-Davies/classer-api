{{-- App Showcase Section --}}
@php
    $devices = [
        [
            'type'  => 'mac',
            'src'   => asset('/assets/images/welcome/hero/image-1.jpg'),
            'alt'   => 'Classer app on Mac showing organised recordings',
            'label' => 'Mac',
        ],
        [
            'type'  => 'windows',
            'src'   => asset('/assets/images/welcome/hero/image-2.jpg'),
            'alt'   => 'Classer app on Windows showing organised recordings',
            'label' => 'Windows',
        ],
        [
            'type'  => 'ipad',
            'src'   => asset('/assets/images/welcome/hero/image-3.jpg'),
            'alt'   => 'Classer app on iPad',
            'label' => 'iPad',
        ],
        [
            'type'  => 'ipad-landscape',
            'src'   => asset('/assets/images/welcome/hero/image-4.jpg'),
            'alt'   => 'Classer app on iPad in landscape mode',
            'label' => 'iPad',
        ],
    ];
@endphp

<style>
    /* ── Device frame animation ── */
    #device-frame {
        transition: max-width 0.7s cubic-bezier(.4,0,.2,1),
                    border-radius 0.7s cubic-bezier(.4,0,.2,1),
                    padding 0.5s ease;
    }
    #device-frame .device-chrome {
        transition: opacity 0.4s ease, height 0.4s ease;
    }
    #device-frame .device-screen {
        transition: border-radius 0.5s ease;
    }

    /* Device sizes */
    .device-size-mac            { max-width: 960px; }
    .device-size-windows        { max-width: 960px; }
    .device-size-ipad           { max-width: 960px; }
    .device-size-ipad-landscape { max-width: 960px; }

    /* ── Mac traffic lights ── */
    .mac-buttons { display: flex; gap: 6px; align-items: center; }
    .mac-buttons span {
        width: 10px; height: 10px; border-radius: 50%; display: inline-block;
    }
    .mac-btn-close    { background: #ff5f57; }
    .mac-btn-minimize { background: #ffbd2e; }
    .mac-btn-maximize { background: #28c840; }

    /* ── Windows buttons ── */
    .win-buttons { display: flex; align-items: center; margin-left: auto; }
    .win-buttons button {
        width: 32px; height: 24px; display: flex; align-items: center; justify-content: center;
        background: transparent; border: none; color: #555; font-size: 12px; cursor: default;
        transition: background 0.15s;
    }
    .win-buttons button:hover { background: rgba(0,0,0,.06); }
    .win-buttons .win-close:hover { background: #e81123; color: #fff; }

    /* ── iPad / iPhone notch ── */
    .apple-notch {
        position: relative;
        display: flex;
        justify-content: center;
        height: 20px;
    }
    .apple-notch::after {
        content: '';
        width: 80px;
        height: 5px;
        background: #1a1a1a;
        border-radius: 10px;
        margin-top: 6px;
    }

    /* iPad landscape notch: same style as portrait */
    .device-size-ipad-landscape .apple-notch::after {
        width: 80px;
        height: 5px;
        border-radius: 10px;
        margin-top: 6px;
    }

    /* Label pill */
    .device-label {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 2px 10px;
        border-radius: 999px;
        background: rgba(0,0,0,.06);
        color: #888;
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    .device-label.visible { opacity: 1; }
</style>

<div class="mx-auto w-full max-w-4xl text-center">

    {{-- Heading --}}
    <h2 class="text-3xl md:text-5xl leading-tight mb-4 text-brand-color font-semibold">
        A home for your memories.<br>
        An app to relive them.
    </h2>

    {{-- Subtitle --}}
    <p class="text-gray-500 text-sm md:text-base max-w-xl mx-auto mb-10 md:mb-14">
        The Classer Home quietly collects and organises your footage in the background and when you are ready, your best moments are waiting in Classer's app.
    </p>
</div>

{{-- Device Frame + Carousel --}}
<div class="relative mx-auto flex justify-center">

    {{-- Animated device frame --}}
    <div id="device-frame" class="device-size-mac w-full rounded-xl md:rounded-2xl border border-gray-200 bg-gray-100 shadow-xl p-2 md:p-3 overflow-hidden relative">

        {{-- Device label --}}
        <span id="device-label" class="device-label visible">Mac</span>

        {{-- ── Mac title bar ── --}}
        <div id="chrome-mac" class="device-chrome flex items-center px-2 pb-2" style="height: 28px;">
            <div class="mac-buttons">
                <span class="mac-btn-close"></span>
                <span class="mac-btn-minimize"></span>
                <span class="mac-btn-maximize"></span>
            </div>
        </div>

        {{-- ── Windows title bar ── --}}
        <div id="chrome-windows" class="device-chrome items-center px-1 pb-2" style="height: 0; opacity: 0; overflow: hidden; display: flex;">
            <span id="device-label-win" class="device-label visible" style="position: static; opacity: 1; margin-left: 4px;">Windows</span>
            <div class="win-buttons">
                <button type="button" aria-label="Minimize">&#8211;</button>
                <button type="button" aria-label="Maximize">&#9633;</button>
                <button type="button" aria-label="Close" class="win-close">&#10005;</button>
            </div>
        </div>

        {{-- ── Apple notch (iPad / iPhone) ── --}}
        <div id="chrome-apple" class="device-chrome apple-notch" style="height: 0; opacity: 0; overflow: hidden;"></div>

        {{-- Screen area --}}
        <div id="device-screen" class="device-screen relative rounded-lg overflow-hidden bg-white">
            {{-- Invisible sizing image --}}
            <img src="{{ $devices[0]['src'] }}" class="w-full opacity-0" alt="">

            {{-- Slides --}}
            <div id="app-showcase-slides" class="absolute inset-0">
                @foreach ($devices as $i => $device)
                    <div class="absolute inset-0 opacity-0 transition-opacity duration-700 ease-in-out {{ $i === 0 ? 'opacity-100' : '' }}">
                        <img src="{{ $device['src'] }}" alt="{{ $device['alt'] }}" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Dot Indicators --}}
<div id="app-showcase-dots" class="flex justify-center mt-6 md:mt-8">
    <div class="inline-flex gap-4 bg-white/60 backdrop-blur-sm rounded-full px-5 py-3 shadow-sm">
        @foreach ($devices as $i => $device)
            <button type="button"
                    aria-label="{{ $device['label'] }}"
                    data-app-showcase-slide="{{ $i }}"
                    class="w-4 h-4 rounded-full transition-colors duration-300 cursor-pointer {{ $i === 0 ? 'bg-brand-color' : 'bg-gray-300 hover:bg-gray-400' }}">
            </button>
        @endforeach
    </div>
</div>

<script>
(function () {
    const slides   = Array.from(document.querySelectorAll('#app-showcase-slides > div'));
    const dots     = Array.from(document.querySelectorAll('[data-app-showcase-slide]'));
    const frame    = document.getElementById('device-frame');
    const label    = document.getElementById('device-label');
    const labelWin = document.getElementById('device-label-win');
    const screen   = document.getElementById('device-screen');
    const chromeMac     = document.getElementById('chrome-mac');
    const chromeWin     = document.getElementById('chrome-windows');
    const chromeApple   = document.getElementById('chrome-apple');

    const devices = @json(array_map(fn($d) => ['type' => $d['type'], 'label' => $d['label']], $devices));

    let current  = 0;
    let interval;

    function showChrome(type) {
        // Hide all chromes
        [chromeMac, chromeWin, chromeApple].forEach(function (el) {
            el.style.height  = '0';
            el.style.opacity = '0';
            el.style.overflow = 'hidden';
        });

        // Show the right chrome
        if (type === 'mac') {
            chromeMac.style.height  = '28px';
            chromeMac.style.opacity = '1';
        } else if (type === 'windows') {
            chromeWin.style.height  = '28px';
            chromeWin.style.opacity = '1';
        } else if (type === 'ipad' || type === 'ipad-landscape') {
            chromeApple.style.height  = '20px';
            chromeApple.style.opacity = '1';
        }
    }

    function setDeviceSize(type) {
        // Remove all size classes
        frame.classList.remove('device-size-mac', 'device-size-windows', 'device-size-ipad', 'device-size-ipad-landscape');
        frame.classList.add('device-size-' + type);

        // Adjust border-radius for tablet/mobile devices
        if (type === 'ipad' || type === 'ipad-landscape') {
            frame.style.borderRadius = '18px';
            screen.style.borderRadius = '10px';
        } else {
            frame.style.borderRadius = '';
            screen.style.borderRadius = '';
        }
    }

    function goTo(index) {
        // Fade out current slide
        slides[current].classList.remove('opacity-100');
        dots[current].classList.remove('bg-brand-color');
        dots[current].classList.add('bg-gray-300');

        current = index;
        var device = devices[current];

        // Update device frame
        setDeviceSize(device.type);
        showChrome(device.type);

        // Update label — for Windows, show inside title bar; for others, floating pill
        if (device.type === 'windows') {
            label.classList.remove('visible');
            labelWin.textContent = device.label;
        } else {
            label.classList.remove('visible');
            setTimeout(function () {
                label.textContent = device.label;
                label.classList.add('visible');
            }, 300);
        }

        // Fade in new slide
        slides[current].classList.add('opacity-100');
        dots[current].classList.remove('bg-gray-300');
        dots[current].classList.add('bg-brand-color');
    }

    function next() {
        goTo((current + 1) % slides.length);
    }

    var dotsContainer = document.getElementById('app-showcase-dots');

    // Pause on hover over dots area
    dotsContainer.addEventListener('mouseenter', function () {
        clearInterval(interval);
    });
    dotsContainer.addEventListener('mouseleave', function () {
        interval = setInterval(next, 6000);
    });

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () {
            clearInterval(interval);
            goTo(i);
        });
    });

    interval = setInterval(next, 6000);
})();
</script>
