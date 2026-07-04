@php
    $hotspots = [
        [
            'position' => [
                'base' => ['top' => '6%', 'left' => '70%'],
                'md'   => ['top' => '43%', 'left' => '18%'],
                'lg'   => ['top' => '38%', 'left' => '18%'],
                'xl'   => ['top' => '40%', 'left' => '50%'],
            ],
            'content' => '
                <p class="font-medium text-white">
                    Classer App
                </p>

                <p class="mt-2 text-white/75 leading-relaxed">
                    A powerful app that lets you relive your adventures across all your devices on Windows, Mac, iOS and Android. No monthly subscriptions required.
                </p>
            ',
        ],
        [
            'position' => [
                'base' => ['top' => '6%', 'left' => '70%'],
                'md'   => ['top' => '63%', 'left' => '82%'],
                'lg'   => ['top' => '69%', 'left' => '65%'],
                'xl'   => ['top' => '80%', 'left' => '55%'],
            ],
            'content' => '
                <p class="font-medium text-white">
                    Classer Home
                </p>

                <p class="mt-2 text-white/75 leading-relaxed">
                    This is a tooltip body. You can include <strong>HTML</strong>,
                    <code>code</code>, links, or small lists here.
                </p>
            ',
        ]
    ];
@endphp

<section class="relative w-full overflow-hidden h-[100vh]">
    {{-- image/background/content here --}}
    <x-hotspots :hotspots="$hotspots" />
     
    {{-- Border around the entire hero section --}}
    <div class="absolute inset-0 z-[4] border-24 border-white pointer-events-none"></div>

    {{-- Dark overlay for legibility --}}
    <div class="absolute inset-0 z-[3] bg-gradient-to-r from-black/85 via-black/55 to-black/10"></div>

    <img
        class="absolute right-0 top-0 w-full h-full w-auto object-cover z-0 scale-[1.05] md:scale-[1.0] lg:scale-[1.6] xl:scale-[1.4]"
        src="{{ asset('assets/images/classer-2/hero.jpg') }}"
        alt="Classer app being used on an iPad"
    />

    {{-- Subtle bottom vignette --}}
    <!-- <div class="absolute inset-x-0 bottom-0 z-[4] h-40 bg-gradient-to-t from-black/100 to-transparent"></div> -->

    {{-- Content --}}
    <section class="w-full px-4 md:px-6 pb-5 relative z-10 h-full flex">
        <header class="mx-auto w-full max-w-7xl mt-[155px] flex flex-col items-center">
            <h1 class="text-white font-medium text-center text-5xl md:text-6xl lg:text-7xl leading-[1.02] tracking-[-0.035em]">
                <span class="opacity-0 -ml-5">.</span>
                <span class="js-typed-adventure">Classer.</span>
            </h1>

            <p class="text-white/85 text-base leading-relaxed max-w-lg text-center mt-3 mb-6">
                <span class="font-bold">Classer</span> built for action camera owners who have thousands of clips in their hard drives and zero time to sort them.
            </p>

            @include('partials.catalog-item-purchase-form', [
                'btnClasses' => 'bg-white text-black shadow-lg shadow-black/20',
                'buttonLabel' => 'Order now',
                'formClass' => '',
                'catalogItemSkus' => [
                    'PRODUCT-J3VQXNTI',
                    'PLAN-NT8P1DOQ',
                ],
            ])
        </header>
    </section>
</section>

<script type="module">
    const typedTarget = document.querySelector('.js-typed-adventure');
    if (typedTarget) {
        new Typed(typedTarget, {
            strings: [
                'Live it.',
                'Love it.',
                'Remember it.',
            ],
            typeSpeed: 35,
            backSpeed: 25,
            backDelay: 700,
            startDelay: 300,
            loop: false,
            smartBackspace: true,
            showCursor: false,
            cursorChar: '|',
        });
    }
</script>