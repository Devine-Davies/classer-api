@php
    $tutorialsItems = [
        [
            'label' => 'Importing videos',
            'url' => 'https://www.youtube.com/watch?v=xahA3lZR3Ew',
            'thumbnail' => @asset('assets/images/welcome/tutorials/importing.png'),
            'alt' => 'A link on how to use Classer to import action camera and drone recordings.',
        ],
        [
            'label' => 'Create and search tags',
            'url' => 'https://www.youtube.com/watch?v=FiSCAcEcodU&ab_channel=Classer',
            'thumbnail' => @asset('assets/images/welcome/tutorials/tags.png'),
            'alt' => 'A link on how to use Classer to create and search tags.',
        ],
        [
            'label' => 'Create moments',
            'url' => 'https://www.youtube.com/watch?v=7KIVe2wPEXc',
            'thumbnail' => @asset('assets/images/welcome/tutorials/create-moment.png'),
            'alt' => 'A link on how to create moments in Classer.',
        ],
        [
            'label' => 'Setting locations',
            'url' => 'https://www.youtube.com/watch?v=LY47F7AWY2s',
            'thumbnail' => @asset('assets/images/welcome/tutorials/maps-no-location.png'),
            'alt' => 'A link on how to set locations in Classer.',
        ],
        [
            'label' => 'Create and assign albums',
            'url' => 'https://www.youtube.com/watch?v=a8yARrL0aYo',
            'thumbnail' => @asset('assets/images/welcome/tutorials/create-and-assign-album.png'),
            'alt' => 'A link on how to assign albums in Classer.',
        ],
        [
            'label' => 'Add favourites',
            'url' => 'https://www.youtube.com/watch?v=dPZmEZ_D7m4',
            'thumbnail' => @asset('assets/images/welcome/tutorials/favs.png'),
            'alt' => 'A link on how to add favourites in Classer.',
        ],
    ];
@endphp

<header class="mb-6 text-center">
    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color">Explore our guides</h1>
</header>

<div class="mb-6 md:mt-16 lg:mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach ($tutorialsItems as $item)
        <div class="group relative overflow-hidden rounded-md hover:underline transition duration-300 bg-white">
            <a href="{{ $item['url'] }}" target="_blank" class="block">
                <div class="relative">
                    <img src="{{ $item['thumbnail'] }}" alt="{{ $item['alt'] }}"
                        class="w-full h-56 object-cover transform transition duration-200 ease-in-out" />
                    <div
                        class="absolute inset-0 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                        <span class="inline-flex items-center text-white text-lg font-semibold">
                            ▶ Watch tutorial
                        </span>
                    </div>
                </div>
                
                <div class="p-4 text-center absolute left-0 right-0 bottom-0 backdrop-blur-sm bg-white/60">
                    <p
                        class="text-md font-medium text-gray-800 group-hover:underline group-hover:text-brand-color transition">
                        {{ $item['label'] }}
                    </p>
                </div>
            </a>
        </div>
    @endforeach
</div>
