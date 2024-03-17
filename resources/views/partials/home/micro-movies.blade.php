@php
    $playIcon =
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -3 24 24" width="28" fill="currentColor"><path d="M13.82 9.523a.976.976 0 0 0-.324-1.363L3.574 2.128a1.031 1.031 0 0 0-.535-.149c-.56 0-1.013.443-1.013.99V15.03c0 .185.053.366.153.523.296.464.92.606 1.395.317l9.922-6.031c.131-.08.243-.189.325-.317zm.746 1.997l-9.921 6.031c-1.425.867-3.3.44-4.186-.951A2.918 2.918 0 0 1 0 15.03V2.97C0 1.329 1.36 0 3.04 0c.567 0 1.123.155 1.605.448l9.921 6.032c1.425.866 1.862 2.696.975 4.088-.246.386-.58.712-.975.952z"></path></svg>';
    $movies = [
        [
            'title' => 'Capture moments, not megabytes',
            'src' => 'https://classermedia.com/assets/videos/welcome/micro-movies/short-1.mp4',
            'poster' => @asset('assets/images/welcome/micro-movies/short-1.jpg'),
        ],
        [
            'title' => 'Every location, tells a story',
            'src' => 'https://classermedia.com/assets/videos/welcome/micro-movies/short-2.mp4',
            'poster' => @asset('assets/images/welcome/micro-movies/short-2.jpg'),
        ],
        [
            'title' => 'MTB',
            'src' => 'https://classermedia.com/assets/videos/welcome/micro-movies/short-3.mp4',
            'poster' => @asset('assets/images/welcome/micro-movies/short-3.jpg'),
        ],
    ];
@endphp

<div class="text-center mb-6 md:mb-8">
    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color">
        Know us more, our 20 second micro-movies
    </h2>
</div>

<div class="flex justify-center gap-8 px-4 m-auto">
    @foreach ($movies as $movie)
        <div @class([
            'relative w-1/2 sm:w-1/3 cursor-pointer' => true,
            'hidden md:block' => $loop->last,
        ])>
            <div class="button absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                <button aria-label="play {{ $movie['title'] }}"
                    class="flex items-center justify-center w-16 h-16 bg-blue-500 rounded-full shadow-2xl scale-125 pointer-events-none">
                    {!! $playIcon !!}
                </button>
            </div>
            <video loop class="rounded-md" muted src="{{ $movie['src'] }}" poster="{{ $movie['poster'] }}"
                alt="{{ $movie['title'] }}" />
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const videos = document.querySelectorAll('video');
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator
            .msMaxTouchPoints > 0;
        const eventTypes = ['click', 'touchstart'];
        const eventType = isTouchDevice ? eventTypes[1] : eventTypes[0];

        videos.forEach(video => video.addEventListener(eventType, event => playMovie(event, video)));
    });

    const playMovie = (event, video) => {
        alert('playMovie');
        const button = event.target.parentElement.querySelector('.button');
        if (video.paused) {
            button.style.display = 'none';
            video.play();
        } else {
            button.style.display = 'block';
            video.pause();
        }
    }
</script>
