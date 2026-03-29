{{-- Hero Banner --}}

@php
    // Add hero images here — each entry needs the base name (without size suffix)
    $heroImages = [
        ['base' => 'hero-2', 'ext' => 'jpg'],
        ['base' => 'hero-3', 'ext' => 'jpg'],
    ];
@endphp

<style>
    .hero-slide {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: 80% 60%;
        opacity: 0;
        transform: scale(1);
        will-change: transform, opacity, mask-position;
    }

    /* Ken Burns — slow zoom while active, with fade-out at end */
    .hero-slide.active {
        opacity: 1;
        /* animation: kenBurnsIn 8s ease-in-out forwards; */
    }

    /* Soft gradient mask wipe for reveal */
    /* .hero-slide.revealing {
        opacity: 1;
        -webkit-mask-image: linear-gradient(to right, #ffffff 0%, #ffffff 30%, transparent 60%);
        mask-image: linear-gradient(to right, #ffffff 0%, #ffffff 30%, transparent 60%);
        -webkit-mask-size: 300% 100%;
        mask-size: 300% 100%;
        -webkit-mask-position: 100% 0;
        mask-position: 100% 0;
        animation: maskReveal 1.8s cubic-bezier(.4, 0, .2, 1) forwards,
                   kenBurnsIn 8s ease-in-out forwards;
    } */

    /* Outgoing slide — gentle fade + continues zoom */
    /* .hero-slide.leaving {
        animation: kenBurnsOut 2.5s ease-out forwards;
    } */

    /* @keyframes kenBurnsIn {
        0%   { transform: scale(1)    translate(0, 0);           opacity: 1; }
        85%  { transform: scale(1.05) translate(-0.4%, -0.25%);  opacity: 1; }
        100% { transform: scale(1.06) translate(-0.5%, -0.3%);   opacity: 0.6; }
    }

    @keyframes kenBurnsOut {
        0%   { transform: scale(1.06) translate(-0.5%, -0.3%); opacity: 0.6; }
        100% { transform: scale(1.08) translate(-0.6%, -0.4%); opacity: 0; }
    } */

    @keyframes maskReveal {
        0%   { -webkit-mask-position: 100% 0; mask-position: 100% 0; }
        100% { -webkit-mask-position: 0% 0;   mask-position: 0% 0; }
    }

    @media (min-width: 600px) {
        .hero-slide { background-position: 65% 65%; }
    }
    @media (min-width: 800px) {
        .hero-slide { background-position: 65% 90%; }
    }
</style>

    {{-- Placeholder keeps the hero height compact while using a background image --}}
    <div aria-hidden="true" class="pointer-events-none invisible select-none"></div>

    {{-- Background image slides --}}
    @foreach ($heroImages as $i => $img)
        <div
            class="hero-slide {{ $i === 0 ? 'active' : '' }}"
            data-hero-slide
            style="background-image: url('{{ asset("/assets/images/classer-home/{$img['base']}.{$img['ext']}") }}');"
            data-img-sm="{{ asset("/assets/images/classer-home/{$img['base']}.{$img['ext']}") }}"
            data-img-md="{{ asset("/assets/images/classer-home/{$img['base']}.{$img['ext']}") }}"
            data-img-lg="{{ asset("/assets/images/classer-home/{$img['base']}.{$img['ext']}") }}"
        ></div>
    @endforeach

    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-neutral-700/10 to-transparent z-10"></div>

    {{-- Content --}}
    <div class="absolute inset-0 z-20 flex items-center md:items-start lg:items-center">
        <div class="mx-auto w-full px-8 md:px-12">
            <div class="md:max-w-7xl m-auto md:mt-8 text-center md:text-left lg:-translate-y-[20%]">
                {{-- Label --}}
                <p class="text-white/80 md:text-xs font-medium tracking-[0.3em] uppercase mb-3" >
                    Classer Home
                </p>

                {{-- Heading --}}
                <h1 class="text-white font-semibold text-3xl md:text-4xl lg:text-5xl leading-tight mb-3">
                    Relive your adventures. <br/>Not manage them.
                </h1>

                {{-- Subtitle --}}
                <p class="text-gray-200 text-xl leading-tight max-w-xl mx-auto md:mx-0 mb-6">
                   Classer Home is the device that look after your memories and bring back to life the moments you care by organising and collecting the best parts.
                </p>

                {{-- CTA Button --}}
                <a href="#early-access"
                   class="inline-block border border-white text-white text-[10px] md:text-xs font-medium tracking-widest uppercase px-5 md:px-6 py-3 rounded-full hover:bg-white hover:text-neutral-800 transition-colors duration-300 bg-white/10">
                    Join early access
                </a>

            </div>
        </div>
    </div>

<script>
(function () {
    var slides = Array.from(document.querySelectorAll('[data-hero-slide]'));
    if (slides.length < 2) return;

    // Set responsive background images
    function setResponsiveImages() {
        var w = window.innerWidth;
        var attr = w >= 1440 ? 'data-img-lg' : (w >= 1080 ? 'data-img-md' : 'data-img-sm');
        slides.forEach(function (slide) {
            slide.style.backgroundImage = "url('" + slide.getAttribute(attr) + "')";
        });
    }

    var current = 0;

    function next() {
        var prev = current;
        current = (current + 1) % slides.length;

        // Outgoing: remove active, add leaving
        slides[prev].classList.remove('active', 'revealing');
        slides[prev].classList.add('leaving');

        // Incoming: mask reveal + Ken Burns
        slides[current].classList.remove('leaving');
        slides[current].classList.add('revealing', 'active');

        // Clean up leaving class after transition
        setTimeout(function () {
            slides[prev].classList.remove('leaving');
        }, 2000);
    }

    setResponsiveImages();
    window.addEventListener('resize', setResponsiveImages);
    setInterval(next, 8000);
})();
</script>
