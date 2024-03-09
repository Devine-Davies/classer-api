<section id="nav" class="w-full sticky top-0 bg-white z-50">
    <a href="{!! url('/') !!}" class="mx-auto p-2 flex flex-wrap items-center justify-center md:justify-start max-w-7xl m-auto" >
        <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/brand/classer-logo.svg') }}" alt="" />
        <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}" alt="" />
    </a>
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

    window.addEventListener('load', checkScroll);
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