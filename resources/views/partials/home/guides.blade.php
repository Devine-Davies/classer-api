@php
    $tutorialsItems = [
        [
            'label' => 'Importing videos',
            'url' => 'https://www.youtube.com/watch?v=xahA3lZR3Ew',
            'thumbnail' => @asset('assets/images/welcome/tutorials/importing.png'),
            'alt' => 'A link on how to use Classer to import action camera and drone recordings.'
        ],
        [
            'label' => 'Create and search tags',
            'url' => 'https://www.youtube.com/watch?v=FiSCAcEcodU&ab_channel=Classer',
            'thumbnail' => @asset('assets/images/welcome/tutorials/tags.png'),
            'alt' => 'A link on how to use Classer to create and search tags.'
        ],
        [
            'label' => 'Create moments',
            'url' => 'https://www.youtube.com/watch?v=7KIVe2wPEXc',
            'thumbnail' => @asset('assets/images/welcome/tutorials/create-moment.png'),
            'alt' => 'A link on how to create moments in Classer.'
        ],
        [
            'label' => 'Setting locations',
            'url' => 'https://www.youtube.com/watch?v=LY47F7AWY2s',
            'thumbnail' => @asset('assets/images/welcome/tutorials/maps-no-location.png'),
            'alt' => 'A link on how to set locations in Classer.'
        ],
        [
            'label' => 'Create and assign albums',
            'url' => 'https://www.youtube.com/watch?v=a8yARrL0aYo',
            'thumbnail' => @asset('assets/images/welcome/tutorials/create-and-assign-album.png'),
            'alt' => 'A link on how to assign albums in Classer.'
        ],
        [
            'label' => 'Add favourites',
            'url' => 'https://www.youtube.com/watch?v=dPZmEZ_D7m4',
            'thumbnail' => @asset('assets/images/welcome/tutorials/favs.png'),
            'alt' => 'A link on how to add favourites in Classer.'
        ],
    ];
@endphp

<h3 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">Explore our guides</h3>
<div class="mb-6 md:mt-16 lg:mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
    @foreach ($tutorialsItems as $item)
        <div class="relative w-full hover:opacity-75 transition-opacity duration-300 ease-in-out rounded-md overflow-hidden">
            <a href="{{ $item['url'] }}" target="_blank" class="absolute top-0 left-0 w-full h-full">
                <span class="sr-only">{{ $item['label'] }}</span>
            </a>
            <img class="w-full" alt="{{ $item['alt'] }}" src="{{ $item['thumbnail'] }}" alt="" />
            <p class="mt-4 text-center mx-auto w-full md:max-w-xs text-xl md:mt-3">{{ $item['label'] }}</p>
        </div>
    @endforeach
</div>
