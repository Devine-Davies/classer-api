{{-- Hero Banner --}}

<style>
    .classer-home-hero-bg {
        background-position: 0 30%;
        background-image: url('{{ asset('/assets/images/classer-home/hero@1080.png') }}');
    }

    @media (min-width: 600px) {
        .classer-home-hero-bg {        
            background-position: 0 45%;
        }
    }

    @media (min-width: 800px) {
        .classer-home-hero-bg {        
            background-position: 0 55%;
        }
    }

    @media (min-width: 1080px) {
        .classer-home-hero-bg {
            background-image: url('{{ asset('/assets/images/classer-home/hero@1440.png') }}');
        }
    }

    @media (min-width: 1440px) {
        .classer-home-hero-bg {
            background-image: url('{{ asset('/assets/images/classer-home/hero@1920.png') }}');
        }
    }
</style>

    {{-- Placeholder keeps the hero height compact while using a background image --}}
    <div aria-hidden="true" class="pointer-events-none invisible select-none"></div>

    {{-- Background image / overlay layers --}}
    <div class="classer-home-hero-bg absolute inset-0 bg-cover bg-center bg-no-repeat"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-neutral-800/80 via-neutral-700/40 to-transparent z-10"></div>

    {{-- Content --}}
    <div class="absolute inset-0 z-20 flex lg:items-center">
        <div class="mx-auto w-full max-w-7xl px-8 md:px-12 py-6 md:py-8">
            <div class="max-w-xl">

                {{-- Label --}}
                <p class="text-white/80 text-[10px] md:text-xs font-medium tracking-[0.3em] uppercase mb-3" >
                    Classer Home
                </p>

                {{-- Heading --}}
                <h1 class="text-white text-4xl md:text-5xl lg:text-5xl xl:text-6xl leading-tight mb-3">
                    The home device that looks after your adventures
                </h1>

                {{-- Subtitle --}}
                <p class="hidden sm:block text-white/70 text-sm md:text-base mb-4">
                    A home device that quietly organises your action camera footage.
                </p>

                {{-- CTA Button --}}
                <a href="#early-access"
                   class="inline-block border border-white text-white text-[10px] md:text-xs font-medium tracking-widest uppercase px-5 md:px-6 py-2 rounded-full hover:bg-white hover:text-neutral-800 transition-colors duration-300">
                    Join Early Access
                </a>

            </div>
        </div>
    </div>
