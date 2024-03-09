@php
    $tutorialsItems = [
        [
            'label' => 'Highlights and exporting',
            'url' => 'https://www.youtube.com/watch?v=BKq31l-p6C4',
            'thumbnail' => @asset('assets/images/welcome/tutorials/highlights@2x-2.png'),
        ],
        [
            'label' => 'Importing',
            'url' => 'https://www.youtube.com/watch?v=pl_H80jAtoE',
            'thumbnail' => @asset('assets/images/welcome/tutorials/importing@2x-2.png'),
        ],
        [
            'label' => 'Create and search tags',
            'url' => 'https://www.youtube.com/watch?v=jPNaHiBkl0s',
            'thumbnail' => @asset('assets/images/welcome/tutorials/search-a-tag@2x-2.png'),
        ],
    ];
@endphp

<h3 class="text-4xl font-bold text-center text-brand-color mb-8">Explore our guides</h3>
<div class="mb-6 md:mt-16 lg:mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
    @foreach ($tutorialsItems as $item)
        <div class="relative w-full hover:opacity-75 transition-opacity duration-300 ease-in-out">
            <a href="{{ $item['url'] }}" target="_blank" class="absolute top-0 left-0 w-full h-full"></a>
            <img class="w-full" src="{{ $item['thumbnail'] }}" alt="" />
            <p class="mt-4 text-center mx-auto w-full md:max-w-xs text-xl md:mt-3">{{ $item['label'] }}</p>
        </div>
    @endforeach
</div>
