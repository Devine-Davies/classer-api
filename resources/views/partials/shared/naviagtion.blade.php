<section id="nav" class="w-full sticky top-0 bg-white z-50">
    <nav class="flex items-center max-w-7xl m-auto p-3 md:justify-between flex-col md:flex-row">
        <div class="flex justify-between  items-center gap-4 w-full md:w-auto">
            <a href="{!! url('/') !!}" class="flex items-center">
                <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/brand/classer-logo.svg') }}"
                    alt="Classer Symbol Logo" />
                <img class="py-2 px-4 w-40 inline-block md:hidden lg:inline-block"
                    src="{{ asset('/assets/images/brand/classer-text.svg') }}" alt="Classer Text Logo" />
            </a>

            <button class="md:hidden hover:bg-gray-100 p-2 rounded-full" data-global-nav-toggle
                aria-label="Gobal navigation state Toggle">
                @icon(menu)
            </button>
        </div>

        <section id="global-nav" class="hidden flex md:flex">
            <a href="{{ url('/') }}/#!/features-section" class="link">Features</a>
            <a href="{{ url('/') }}/#!/how-it-works-section" class="link">How it works</a>
            <a href="{{ url('/') }}/#!/micro-movies-section" class="link">Micro movies</a>
            <a href="{{ url('/') }}/#!/pricing-models-section" class="link">Pricing</a>
            <a href="{{ url('/') }}/#!/our-stories-section" class="link">Blog</a>
            <a href="{{ url('/') }}/action-camera-matcher" class="link ">Action Camera
                Matcher</a>
            <a aria-label="Download Classer" href="?modal=download" data-modal-open
                class="btn inline py-1 px-2 text-sm md:hidden lg:inline">
                Download
            </a>
        </section>
    </nav>
</section>