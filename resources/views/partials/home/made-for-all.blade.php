@php
    $activities = [
        [
            'title' => 'Hiking',
            'image' => 'hiking@2x.jpg',
            'alt' => 'A hiker on a mountain peak, captured with GoPro',
        ],
        [
            'title' => 'Kayaking',
            'image' => 'kayaking@2x.jpg',
            'alt' => 'A kayaker on a river, captured with action camera',
        ],
        [
            'title' => 'MTB',
            'image' => 'mtb@2x.jpg',
            'alt' => 'A mountain biker on a trail, captured with action camera',
        ],
        [
            'title' => 'Snorkel',
            'image' => 'snorkel@2x.jpg',
            'alt' => 'A snorkeler in the ocean, captured with DJI Osmo Action',
        ],
        [
            'title' => 'Surf',
            'image' => 'surf@2x.jpg',
            'alt' => 'A point of view shot of a surfer on a wave, captured with GoPro',
        ],
        [
            'title' => 'Travel',
            'image' => 'travel@2x.jpg',
            'alt' => 'A hotair balloon ride, captured with Insta360 One X',
        ],
    ];
@endphp

<div class="mx-auto max-w-screen-md text-center mb-6 md:mb-8">
    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-brand-color mb-3">
        Perfect for all activities
    </h2>
    <p class="hidden lg:text-xl">Using a single, uniform and elegant interface. Its unified UI design and innovative
        features makes exploring your favourite footage faster, easier and fun.</p>
    <p class="lg:text-xl">Classer is the perfect companion to your action camera recordings. By simply importing your videos you can track route information, speed and places you visited.</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mx-auto">
    @foreach ($activities as $activity)
        <div class="relative">
            <img class="rounded-md" src="{{ asset('assets/images/welcome/activities/' . $activity['image']) }}"
                alt="{{ $activity['alt'] }}" class="w-full h-auto" />
            <p class="absolute bottom-4 w-full text-center text-xl tracking-widest text-white uppercase">
                {{ $activity['title'] }}</p>
        </div>
    @endforeach
</div>
