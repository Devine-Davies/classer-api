{{-- Insights from our early users --}}
@php
    $insights = [
        ['name' => 'Name', 'rating' => 5, 'description' => 'Description'],
        ['name' => 'Name', 'rating' => 5, 'description' => 'Description'],
        ['name' => 'Name', 'rating' => 5, 'description' => 'Description'],
        ['name' => 'Name', 'rating' => 5, 'description' => 'Description'],
    ];
@endphp

<div class="mx-auto w-full max-w-6xl px-6 md:px-8">

    <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight text-center mb-12 md:mb-16 text-brand-color">
        Insights from our early users
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($insights as $item)
            <div class="border border-gray-200 rounded-2xl p-6 bg-white">
                <p class="text-brand-color font-semibold mb-2">{{ $item['name'] }}</p>

                {{-- Stars --}}
                <div class="flex gap-1 mb-3 text-amber-400">
                    @for ($i = 0; $i < $item['rating']; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path d="M10 15.27 16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"/>
                        </svg>
                    @endfor
                </div>

                <p class="text-gray-500 text-sm leading-relaxed">{{ $item['description'] }}</p>
            </div>
        @endforeach
    </div>

</div>
