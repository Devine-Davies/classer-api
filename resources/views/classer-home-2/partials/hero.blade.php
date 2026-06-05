{{-- Hero — full-bleed background image with overlayed copy on the left --}}
<style>
    .hero2-bg {
        background-image: url('https://placeholders.io/1800/1000');
        background-size: cover;
        background-position: center;
    }
</style>

<div class="relative w-full h-[60svh] min-h-[420px] md:min-h-[500px] overflow-hidden hero2-bg">

    {{-- Left-to-right dark fade so the copy stays legible --}}
    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/10"></div>

    {{-- Content — vertically centered within the shared page width --}}
    <div class="relative z-10 h-full flex items-center">
        <div class="mx-auto w-full max-w-7xl px-6 md:px-0 my-4">
            <div class="max-w-2xl">

                <h1 class="text-white max-w-2xl text-4xl md:text-5xl leading-[1.1] font-semibold mb-8">
                    We record everything. We remember almost nothing.
                </h1>

                <p class="text-white/90 text-base md:text-lg leading-relaxed max-w-sm mb-10">
                    Classer is the home for your adventures. It quietly takes care of your footage in the background, so you can focus on living in the moment.
                </p>

                <a href="#early-access"
                   class="inline-block bg-brand-color text-white text-[10px] md:text-xs font-medium tracking-widest uppercase px-7 py-3.5 rounded-full hover:opacity-90 transition-opacity duration-300">
                    Join early access
                </a>

            </div>

        </div>
    </div>

</div>
