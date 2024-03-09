@php 
    $starIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="28" fill="gold"><path d="M10 16.207l-6.173 3.246 1.179-6.874L.01 7.71l6.902-1.003L10 .453l3.087 6.254 6.902 1.003-4.995 4.869 1.18 6.874z"></path></svg>';    
    $insights = [
        [
            'name' => 'Hiking',
            'description' => 'I love the way Classer helps me to organize my hiking videos. I can easily find the best moments and share them with my friends.',
            'rating' => 5,
        ],
        [
            'name' => 'Kayaking',
            'description' => 'I love the way Classer helps me to organize my kayaking videos. I can easily find the best moments and share them with my friends.',
            'rating' => 5,
        ],
        [
            'name' => 'MTB',
            'description' => 'I love the way Classer helps me to organize my MTB videos. I can easily find the best moments and share them with my friends.',
            'rating' => 5,
        ],
        [
            'name' => 'Snorkel',
            'description' => 'I love the way Classer helps me to organize my snorkel videos. I can easily find the best moments and share them with my friends.',
            'rating' => 5,
        ]
    ];
@endphp

<h2 class="text-5xl font-bold text-brand-color mb-2 text-center" >Insights from our early adopters</h2>
<div class="flex justify-center gap-8 mt-8">
    @foreach ($insights as $insight)
        <div class="w-1/2 sm:w-1/3 relative">
            <div class="border p-4 rounded-md flex flex-col gap-y-3">
                <div class="flex justify-center gap-2">
                    @for ($i = 0; $i < $insight['rating']; $i++)
                        {!! $starIcon !!}
                    @endfor
                </div>
                <p>{{ $insight['description'] }}</p>
                <p>{{ $insight['name'] }}</p>
            </div>
        </div>
    @endforeach
</div>