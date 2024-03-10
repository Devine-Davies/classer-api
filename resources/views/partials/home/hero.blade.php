<div class="text-center py-4">
    <h2 class="text-4xl md:text-6xl lg:text-7xl mt-4">
        <span class="font-extrabold block">
            Capture moments,
        </span>
        <span class="mb-4 mt-2 font-thin tracking-tight block text-4xl md:text-6xl lg:text-7xl">
            not megabytes
        </span>
    </h2>
    <p class="text-xl md:text-1xl mt-4 mb-12">
        Make the most of your action camera and drone recordings
    </p>
    <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
        class="btn inline font-semibold text-white py-5 px-12 rounded-full cursor-pointer">
        Get Classer for Free
    </a>
    <p class="mt-6">Available for <span class="font-semibold" >Mac</span> and <span class="font-semibold" >Windows<span></p>
</div>

<div id="carousel" class="relative xl:mt-8 mx-4 md:max-w-5xl md:m-auto">
    <img src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" class="opacity-0 w-full max-w-7xl" alt="...">
    <div id="slides" class="absolute w-full max-w-7xl top-0 left-1/2 -translate-x-1/2 ">
        <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
            <img src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" alt="...">
        </div>
        <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
            <img src="{{ asset('/assets/images/welcome/hero/image-2.jpg') }}" alt="...">
        </div>
        <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
            <img src="{{ asset('/assets/images/welcome/hero/image-3.jpg') }}" alt="...">
        </div>
    </div>
    <div id="indicators" class="absolute z-10 flex gap-6 justify-center w-full mt-8">
        <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="true"
            aria-label="Slide 1" data-carousel-slide-to="0"></button>
        <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="false"
            aria-label="Slide 2" data-carousel-slide-to="1"></button>
        <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="false"
            aria-label="Slide 3" data-carousel-slide-to="2"></button>
    </div>
</div>

<script>
    let slides = Array.from(document.querySelectorAll('#slides > div'));
    let indicators = Array.from(document.querySelectorAll('#indicators > button'));
    let currentSlide = 0;
    let slideInterval;

    const goToSlide = (slide) => {
        slides[currentSlide].classList.remove('opacity-100');
        indicators[currentSlide].classList.remove('bg-brand-color');
        currentSlide = slide;
        slides[currentSlide].classList.add('opacity-100');
        indicators[currentSlide].classList.add('bg-brand-color');
    }

    const nextSlide = () => {
        let newSlide = currentSlide + 1;
        if (newSlide === slides.length) newSlide = 0;
        goToSlide(newSlide);
    }

    indicators.forEach((indicator, i) => {
        indicator.addEventListener('click', () => {
            clearInterval(slideInterval);
            goToSlide(i);
            slideInterval = setInterval(nextSlide, 10000);
        });
    });

    goToSlide(0);
</script>
