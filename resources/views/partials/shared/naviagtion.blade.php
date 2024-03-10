<section id="nav" class="w-full sticky top-0 bg-white z-50">
    <nav class="flex justify-between items-center max-w-7xl m-auto p-2">
        <a href="{!! url('/') !!}" class="flex items-center" >
            <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/brand/classer-logo.svg') }}" alt="" />
            <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}" alt="" />
        </a>

        <section class="flex flex-wrap justify-center md:justify-end">
            <a href="#features-section" class="nav-btn hidden md:block text-brand-color font-semibold text-sm px-4 py-2">Features</a>
            <a href="#how-it-works-section" class="nav-btn hidden md:block text-brand-color text-sm px-4 py-2">How it works</a>
            <a href="#micro-movies-section" class="nav-btn hidden md:block text-brand-color text-sm px-4 py-2">Micro movies</a>
            <a href="#pricing-models-section" class="nav-btn hidden md:block text-brand-color text-sm px-4 py-2">Pricing</a>
            <a href="#insights-section" class="nav-btn hidden md:block text-brand-color text-sm px-4 py-2">Insights</a>
            <a href="#f-a-q-section" class="nav-btn hidden md:block text-brand-color text-sm px-4 py-2">F&Q</a>
        </section>
    </nav>
</section>

<script>
    const checkScroll = () => {
        var nav = document.getElementById('nav');
        if (window.pageYOffset > 0) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }

    const goToSection = () => {
        // get nav-btn elements
        const navBtns = document.querySelectorAll('.nav-btn');

        // loop through nav-btn elements
        navBtns.forEach((btn) => {
            // add event listener to each nav-btn element
            btn.addEventListener('click', () => {
                // get the href attribute of the clicked nav-btn element
                const href = btn.getAttribute('href');
                // get the element with the id that matches the href attribute
                const section = document.querySelector(href);
                // scroll to the section
                section.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    }

    window.addEventListener('load', () => {
        checkScroll();
        goToSection();
    });
    window.addEventListener('scroll', checkScroll);
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>