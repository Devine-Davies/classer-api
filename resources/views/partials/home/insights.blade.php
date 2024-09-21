@php 
    $insights = [
        [
            'name' => 'James Tekas',
            'description' => 'Classer has been a total lifesaver for me in managing all my Insta360 footage. Now, I can quickly whip up and share the coolest moments from my outdoor adventures without breaking a sweat!',
            'rating' => 5,
        ],
        [
            'name' => 'Manuel',
            'description' => 'Discovering Classer transformed my GoPro adventures. Now, every MTB moment  is organized and ready to share with my mates!',
            'rating' => 5,
        ],
        [
            'name' => 'Seren Giles',
            'description' => 'I never thought organizing my family\'s memories could be so enjoyable until I started using Classer. From birthdays to vacations, I now have all our precious moments neatly sorted.',
            'rating' => 5,
        ],
        [
            'name' => 'Billy O\'Moore',
            'description' => 'Being a travel blogger, I often found myself drowning in a sea of videos from my trips. Thanks to Classer, I can easily curate stunning visual stories, captivating my audience with every adventure I share.',
            'rating' => 5,
        ]
    ];
@endphp

<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-brand-color mb-8 text-center" >Insights from our early adopters</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($insights as $insight)
        <div @class([
            'relative' => true,
            'hidden md:block' => $loop->last,
        ])>
            <div class="border p-4 rounded-md flex flex-col gap-y-3 h-full">
                <div class="flex justify-center gap-2">
                    @for ($i = 0; $i < $insight['rating']; $i++)
                        @icon(star)
                    @endfor
                </div>
                <p class="h-full" >{{ $insight['description'] }}</p>
                <p class="font-semibold" >{{ $insight['name'] }}</p>
            </div>
        </div>
    @endforeach
</div>