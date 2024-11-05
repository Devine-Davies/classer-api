@php
    $featuresData = [
        [
            'title' => 'Navigate and explore your videos like a pro',
            'imgSrc' => 'features/feature-3.png',
            'imgAlt' => 'A screenshot showcasing a video overview panel',
            'items' => [
                'Get started quickly with multiple video import options',
                'Create and assign categories to help organise your videos',
                'Apply tags so you can quickly search your video library',
                'Stunning visuals and user-driven layouts to work for you',
            ],
        ],
        [
            'title' => 'Track, save and remember places you been',
            'imgSrc' => 'features/feature-4.png',
            'imgAlt' => 'A screenshot showcasing a video overview panel',
            'items' => [
                'Use the map view to track places you visited',
                'Easily update video locations with our drag and drop feature'
            ],
        ],
        [
            'title' => 'Found a favourite moment? Simply save it! Share it!',
            'imgSrc' => 'features/feature-1.png',
            'imgAlt' => 'A screenshot showcasing a capture of a action canera video',
            'items' => [
                'Use our custom capture feature to save your best moments',
                'Create new videos and store them in the cloud',
                'Keep things private or share them with your audience',
                'Save on cloud costs by storing only what you choose',
            ],
        ],
        [
            'title' => 'Discover more with telemetry',
            'imgSrc' => 'features/feature-2.png',
            'imgAlt' => 'A screenshot showcasing a speed of a moutain biker',
            'items' => [
                'Get insights through GPS, Maps, Speed',
                'Time track your runs, surf, rides and more',
                'Track places you been and search them on our map view'
            ],
        ]
    ];
@endphp

<div class="text-center text-brand-color mt-4 mb-6 md:mb-8 max-w-3xl m-auto">
    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold">Here to revolutionise the way you experience your videos</h1>
    <p class="hidden lg:text-xl mt-4">Classer is an dektop app packed full of features to help you explore, save and share your best moments. You can 
</div>

<div class="container m-auto max-w-7xl flex flex-col gap-8">
    @foreach ($featuresData as $i => $feature)
        <article @class([
            'md:flex md:flex-nowrap m-auto md:mt-24 xl:mt-24 2xl:mt-32' => true,
            'flex-row-reverse' => $i % 2 !== 1,
        ])>
            <div class="w-full md:w-10/12">
                <img class="scale-110 md:scale-125" src="{{ asset('assets/images/welcome/' . $feature['imgSrc']) }}"
                    alt="{{ $feature['imgAlt'] }}" />
            </div>
            <div class="place-self-center">
                <div class="place-self-center px-12">
                    <h3
                        class="leading-tight my-6 lg:mt-0 text-brand-color text-xl md:text-2xl lg:text-4xl font-semibold text-center md:text-left ">
                        {{ $feature['title'] }}
                    </h3>
                    @foreach ($feature['items'] as $item)
                        <div class="mb-4 flex justify-start gap-2 items-center">
                            <span class="w-6 h-6">
                                @icon(star)
                            </span>
                            <p>{{ $item }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    @endforeach
</div>
