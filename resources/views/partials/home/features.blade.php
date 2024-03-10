@php
    $featuresData = [
        [
            'title' => 'Discover, collect and manage all your recordings in full detail',
            'imgSrc' => 'features/feature-1.png',
            'imgAlt' => 'image description',
            'listItems' => [
                [
                    'text' => 'Get a better overview of all your videos',
                    'icon' => 'jam-icons/icons/camera-f.svg',
                ],
                [
                    'text' => 'Find and easily capture the moments that matter',
                    'icon' => 'jam-icons/icons/camera-f.svg',
                ],
                [
                    'text' => 'View all your metadata',
                    'icon' => 'jam-icons/icons/info.svg',
                ],
                [
                    'text' => 'Watch your videos in x2 speed',
                    'icon' => 'jam-icons/icons/fast-f.svg',
                ],
            ],
        ],
        [
            'title' => 'Get insights through your telemetry',
            'imgSrc' => 'features/feature-2.png',
            'imgAlt' => 'image description',
            'listItems' => [
                [
                    'text' => 'Learn about your speed',
                    'icon' => 'jam-icons/icons/folder-open.svg',
                ],
                [
                    'text' => 'Get a view of all the places you have been',
                    'icon' => 'jam-icons/icons/hashtag.svg',
                ],
                [
                    'text' => 'View all your metadata',
                    'icon' => 'jam-icons/icons/pin-f.svg',
                ],
            ],
        ],
        [
            'title' => 'Organise and find your memories',
            'imgSrc' => 'features/feature-3.png',
            'imgAlt' => 'image description',
            'listItems' => [
                [
                    'text' => 'Search by tags',
                    'icon' => 'jam-icons/icons/scissors.svg',
                ],
                [
                    'text' => 'Pin videos for a quick and simple navigation',
                    'icon' => 'jam-icons/icons/download.svg',
                ],
                [
                    'text' => 'Make that moment that matter a favourite',
                    'icon' => 'jam-icons/icons/download.svg',
                ],
            ],
        ],
    ];
@endphp

<div class="text-center text-brand-color mt-4 mb-6 md:mb-24 max-w-2xl m-auto">
    <h1 class="text-3xl md:text-5xl font-bold">The essential accessory for your action camera & drones</h1>
    <p class="text-1xl mt-4">Our mission is to revolutionize the way outdoor people manage, store, and
        share their memories with action cameras and drones.</p>
</div>

<div class="container m-auto max-w-7xl">
    @foreach ($featuresData as $i => $feature)
        <article @class([
            'md:flex md:flex-nowrap m-auto md:mt-24 xl:mt-24 2xl:mt-32' => true,
            'flex-row-reverse' => $i % 2 !== 0,
        ])>
            <div class="place-self-center">
                <div class="place-self-center m-auto">
                    <h3
                        class="leading-tight my-6 lg:mt-0 text-brand-color text-2xl lg:text-4xl font-semibold">
                        {{ $feature['title'] }}
                    </h3>
                    @foreach ($feature['listItems'] as $item)
                        <p class="mb-4">
                            <img class="inline text-brand-color"
                                src="{{ asset('assets/images/' . $item['icon']) }}" alt="" />
                            {{ $item['text'] }}
                        </p>
                    @endforeach
                </div>
            </div>
            <div class="w-full md:w-11/12">
                <img class="md:scale-125" src="{{ asset('assets/images/welcome/' . $feature['imgSrc']) }}" alt="{{ $feature['imgAlt'] }}" />
            </div>
        </article>
    @endforeach
</div>
