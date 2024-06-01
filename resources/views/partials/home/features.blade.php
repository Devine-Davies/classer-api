@php
    $featuresData = [
        [
            'title' => 'Intuitive and visually-driven layout, watch and highlight your best moments',
            'imgSrc' => 'features/feature-1.png',
            'imgAlt' => 'A screenshot showcasing a capture of a action canera video',
            'listItems' => [
                [
                    'text' => 'Get a better overview of all your videos',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'Find and easily capture the moments that matter',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'Watch your videos in x2 speed',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
            ],
        ],
        [
            'title' => 'Get better through your telemetry',
            'imgSrc' => 'features/feature-2.png',
            'imgAlt' => 'A screenshot showcasing a speed of a moutain biker',
            'listItems' => [
                [
                    'text' => 'Learn about your speed',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'Get a view of all the places you have been',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'View all your metadata',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
            ],
        ],
        [
            'title' => 'Organise and find your memories',
            'imgSrc' => 'features/feature-3.png',
            'imgAlt' => 'A screenshot showcasing a video overview panel',
            'listItems' => [
                [
                    'text' => 'Search by tags',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'Pin videos for a quick and simple navigation',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
                [
                    'text' => 'Make that moment that matter a favourite',
                    'icon' => 'jam-icons/icons/star-f.svg',
                ],
            ],
        ],
    ];
@endphp

<div class="text-center text-brand-color mt-4 mb-6 md:mb-24 max-w-3xl m-auto">
    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold">The essential accessory for your action camera & drones</h1>
    <p class="lg:text-xl mt-4">Our mission is to revolutionise the way outdoor people manage, store, and
        share their memories with action cameras and drones.</p>
</div>

<div class="container m-auto max-w-7xl">
    @foreach ($featuresData as $i => $feature)
        <article @class([
            'md:flex md:flex-nowrap m-auto md:mt-24 xl:mt-24 2xl:mt-32' => true,
            'flex-row-reverse' => $i % 2 !== 1,
        ])>
            <div class="w-full md:w-11/12">
                <img class="scale-110 md:scale-125" src="{{ asset('assets/images/welcome/' . $feature['imgSrc']) }}" alt="{{ $feature['imgAlt'] }}" />
            </div>
            <div class="place-self-center">
                <div class="place-self-center m-auto">
                    <h3
                        class="leading-tight my-6 lg:mt-0 text-brand-color text-xl md:text-2xl lg:text-4xl font-semibold text-center md:text-left ">
                        {{ $feature['title'] }}
                    </h3>
                    @foreach ($feature['listItems'] as $item)
                        <p class="mb-4">
                            <svg class="inline relative -top-1 star-icon-color" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="28" fill="#333"><path d="M10 16.207l-6.173 3.246 1.179-6.874L.01 7.71l6.902-1.003L10 .453l3.087 6.254 6.902 1.003-4.995 4.869 1.18 6.874z"></path></svg>
                            {{ $item['text'] }}
                        </p>
                    @endforeach
                </div>
            </div>
        </article>
    @endforeach
</div>
