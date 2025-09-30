@php
    $reviews = [
        [
            'name' => 'James Tekas',
            'date' => 'February 7, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=10', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Classer has been a total lifesaver for me in managing all my Insta360 footage. Now, I can quickly whip up and share the coolest moments from my outdoor adventures without breaking a sweat!',
        ],
        [
            'name' => 'Manuel',
            'date' => 'February 1, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=20', // No avatar, use initials or number instead
            'rating' => 4,
            'text' => 'Discovering Classer transformed my GoPro adventures. Now, every MTB moment  is organized and ready to share with my mates!',
        ],
        [
            'name' => 'Seren Giles',
            'date' => 'January 23, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=30', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'I never thought organizing my family\'s memories could be so enjoyable until I started using Classer. From birthdays to vacations, I now have all our precious moments neatly sorted.',
        ],
        [
            'name' => 'Billy O\'Moore',
            'date' => 'January 23, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=40', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Being a travel blogger, I often found myself drowning in a sea of videos from my trips. Thanks to Classer, I can easily curate stunning visual stories, captivating my audience with every adventure I share.',
        ],
        [
            'name' => 'Tamsyn Lana',
            'date' => 'January 23, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=50', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ],
        [
            'name' => 'Mylo Barclay',
            'date' => 'January 23, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=60', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ],
        [
            'name' => 'Rain Miley',
            'date' => 'January 23, 2023',
            'avatar' => 'https://i.pravatar.cc/200?img=70', // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do incididunt ut labore et dolore magna aliqua.',
        ],
            [
            'name' => 'Alexina Rod',
            'date' => 'January 23, 2023',
            'avatar' => null, // No avatar, use initials or number instead
            'rating' => 5,
            'text' => 'Expo movers is the best. The guys are very professional with excellent customer service.',
        ],
        // ...add as many reviews as you want
    ];
@endphp

<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-brand-color mb-8 text-center" >Insights from our users</h2>
<section class="test" class="py-10">
    <!-- Horizontal Scroll Wrapper -->
    <div class="flex overflow-x-auto space-x-6 no-scrollbar pb-4">
        @foreach ($reviews as $review)
            <div class="w-80 flex-shrink-0 bg-white rounded-lg shadow p-5">
                <div class="flex items-center mb-3">
                    @if ($review['avatar'])
                        <img src="{{ $review['avatar'] }}" alt="avatar" class="w-10 h-10 rounded-full mr-3">
                    @else
                        <div class="bg-orange-500 text-white w-10 h-10 flex items-center justify-center rounded-full mr-3">
                            {{ strtoupper(substr($review['name'], 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="font-semibold">{{ $review['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $review['date'] }}</p>
                    </div>
                </div>
                <div class="mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $review['rating'])
                            <span class="text-yellow-400">★</span>
                        @else
                            <span class="text-gray-300">★</span>
                        @endif
                    @endfor
                </div>
                <p class="">
                    {{ $review['text'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>

<style>
    /* Hide scrollbar but keep scroll functionality */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .test {
        position: relative;
    }

    .test::before, .test::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 250px;
        pointer-events: none;
    }
    .test::before {
        left: 0;
        background: linear-gradient(to right, white, rgba(255, 255, 255, 0));
    }
    .test::after {
        right: 0;
        background: linear-gradient(to left, white, rgba(255, 255, 255, 0));
    }
</style>

 {{-- @php 
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

<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-brand-color mb-8 text-center" >Insights from our users</h2>
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
</div> --}}
