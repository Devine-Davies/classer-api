@php
    $images = [
        [
            'src' => asset('/assets/images/welcome/hero/image-1.jpg'),
            'alt' => 'A screen shot of the classer app, showing all the action camera recordings'
        ],
        [
            'src' => asset('/assets/images/welcome/hero/image-2.jpg'),
            'alt' => 'A screen shot of the classer app in dark mode, showing all the action camera recordings'
        ],
        [
            'src' => asset('/assets/images/welcome/hero/image-3.jpg'),
            'alt' => 'A screen shot of the view media screen in the classer app in dark mode'
        ]  
    ];
@endphp

<div class="text-center pt-4 py-8">
    <h2 class="text-4xl md:text-6xl mt-4">
        <span class="font-extrabold block">
            Capture moments,
        </span>
        <span class="mb-4 mt-2 font-thin tracking-tight block">
            not megabytes
        </span>
    </h2>
    <p class="text-base max-w-sm m-auto md:max-w-xl md:text-xl mt-4">
        Make the most of your action camera and drone recordings
    </p>
    <div class="flex justify-center items-center gap-4 mt-12 mb-6">
        <div class="relative" >
            <a aria-label="Download Classer" href="?modal=download" data-modal-open
            class="btn inline font-semibold text-white py-4 px-8 rounded-full cursor-pointer text-xl">
            Download for free
            </a>
            <p class="mt-4 text-sm absolute w-full text-center">For
                <span class="font-semibold">Mac</span> & <span
                class="font-semibold">Windows</span>
            </p>
        </div>

        <div class="scale-90" >
            <a aria-label="Download Classer" href="/auth/register"
                class="btn-outline inline text-white py-4 px-8 rounded-full cursor-pointer text-base">
                Register for free
            </a>
        </div>
    </div>
</div>

<div id="carousel" class="relative xl:mt-8 mx-4 md:mx-6 xl:m-auto">
    <img src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" class="opacity-0 w-full max-w-7xl"
        alt="A screen shot of the classer app">
    <div id="slides" class="absolute w-full h-full max-w-7xl top-0 left-1/2 -translate-x-1/2 ">
        @foreach ($images as $image)
            <div class="h-auto w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
                <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}"  >
            </div>
        @endforeach
    </div>

    <div id="indicators" class="relative m-auto text-center" >
        <div class="inline-flex gap-5 justify-center bg-white rounded-md shadow-sm p-3 px-5" >
            @foreach ($images as $i => $image)
                <button type="button" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                    aria-label="Slide {{ $i + 1 }}" data-carousel-slide-to="{{ $i }}"
                    class=" w-5 h-5 rounded-full bg-zinc-300 hover:bg-zinc-950 relative">
                </button>
            @endforeach
        </div>
    </div>
</div>

<script>
    let slides = Array.from(document.querySelectorAll('#slides > div'));
    let indicators = Array.from(document.querySelectorAll('#indicators button'));
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
