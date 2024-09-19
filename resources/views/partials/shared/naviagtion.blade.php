<section id="nav" class="w-full sticky top-0 bg-white z-50">
    <nav class="flex justify-between items-center max-w-7xl m-auto p-2">
        <a href="{!! url('/') !!}" class="flex items-center">
            <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/brand/classer-logo.svg') }}"
                alt="Classer Symbol Logo" />
            <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}"
                alt="Classer Text Logo" />
        </a>

        <section class="flex flex-wrap justify-center md:justify-end">
            <a href="{{ url('/') }}/#!/features-section" class="nav-link">Features</a>
            <a href="{{ url('/') }}/#!/how-it-works-section" class="nav-link">How it works</a>
            <a href="{{ url('/') }}/#!/micro-movies-section" class="nav-link">Micro movies</a>
            <a href="{{ url('/') }}/#!/pricing-models-section" class="nav-link">Pricing</a>
            <a href="{{ url('/') }}/#!/our-stories-section" class="nav-link">Blog</a>
            <a href="{{ url('/') }}/action-camera-matcher" class="nav-link">Action Camera Matcher</a>
            <a aria-label="Download Classer" href="?modal=download" data-modal-open
                class="btn inline text-white py-2 px-4 rounded-full text-sm">
                Download
            </a>
        </section>
    </nav>
</section>

<script>
    const checkScroll = () => {
        var nav = document.getElementById('nav');
        window.pageYOffset ?
            nav.classList.add('scrolled') :
            nav.classList.remove('scrolled');
    }

    const detectHashBangNavigation = () => {
        const elm = document.getElementById(window.location.hash.replace('#!/', ''));

        elm && scrollToSection(elm);
        window.onpopstate = () => {
            const section = window.location.hash.replace('#!/', '');
            try {
                const element = document.getElementById(section);
                scrollToSection(element);
            } catch (error) {}
        }
    }

    const scrollToSection = (element) => {
        element.classList.add('active');
        element.scrollIntoView({
            behavior: 'smooth'
        });
    }

    window.addEventListener('load', () => {
        detectHashBangNavigation();
        window.addEventListener('scroll', checkScroll);
    });
</script>
