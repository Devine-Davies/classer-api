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
        ],
        [
            'src' => asset('/assets/images/welcome/hero/image-4.jpg'),
            'alt' => 'A screen shot of the view media screen in the classer app in dark mode'
        ],
        [
            'src' => asset('/assets/images/welcome/hero/image-5.jpg'),
            'alt' => 'A screen shot of the view media screen in the classer app in dark mode'
        ]  
    ];

    $triangles = [
        'al fg sm',
        '',
        'al fg md',
        'lg',
        'al fg',
    ];
@endphp

<div class="text-center pt-4 py-8">
    <h2 class="text-4xl md:text-6xl mt-4">
        <span class="font-extrabold block">
            Capture moments
        </span>
        <span class="mb-4 mt-2 font-thin tracking-tight block">
            not megabytes
        </span>
    </h2>
    <p class="text-base max-w-sm m-auto md:max-w-2xl md:text-xl mt-4">
        Classer is the perfect companion to your action camera recordings. Easily create, manage, store, and share your videos.
    </p>
    <div class="flex justify-center items-center gap-4 mt-12 mb-6">
        <div class="relative" >
            <a aria-label="Download Classer" href="?modal=download" data-modal-open
            class="btn btn--xl" >   
            Download for free
            </a>
            <p class="mt-4 text-sm absolute w-full text-center">For
                <span class="font-semibold">Mac</span> & <span
                class="font-semibold">Windows</span>
            </p>
        </div>

        <div class="scale-90" >
            <a aria-label="Download Classer" href="/auth/register"
                class="btn-outline inline text-white py-4 px-3 md:px-8 rounded-full cursor-pointer text-base">
                Register for free
            </a>
        </div>
    </div>
</div>

<div id="carousel" class="relative xl:mt-8 mx-4 md:mx-6 xl:m-auto">
    <img src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" class="opacity-0 w-full max-w-[90rem]"
        alt="A screen shot of the classer app">
    <div id="slides" class="absolute w-full h-full max-w-[90rem] top-0 left-1/2 -translate-x-1/2 ">
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

    <div class="absolute bottom-0 w-full -z-[1]">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#f7f8fa" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,181.3C384,149,480,75,576,69.3C672,64,768,128,864,160C960,192,1056,192,1152,176C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
        <div class="w-full h-36" style="background-color: #f7f8fa"></div>	
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
            // slideInterval = setInterval(nextSlide, 10000);
        });
    });

    goToSlide(0);
</script>
