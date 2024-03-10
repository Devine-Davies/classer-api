@php
    $activities = [
        [
            'title' => 'Hiking',
            'image' => 'hiking@2x.jpg',
        ],
        [
            'title' => 'Kayaking',
            'image' => 'kayaking@2x.jpg',
        ],
        [
            'title' => 'MTB',
            'image' => 'mtb@2x.jpg',
        ],
        [
            'title' => 'Snorkel',
            'image' => 'snorkel@2x.jpg',
        ],
        [
            'title' => 'Surf',
            'image' => 'surf@2x.jpg',
        ],
        [
            'title' => 'Travel',
            'image' => 'travel@2x.jpg',
        ],
    ];
@endphp

<div class="mx-auto max-w-screen-md text-center mb-6 md:mb-8">
    <h2 class="text-4xl md:text-5xl font-bold text-brand-color mb-3">
        Made for all your activities
    </h2>
    <p>Using a single, uniform and elegant interface. Its unified UI design and innovative features make exploring your favorite footage faster, more intuitive and fun.</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 max-w-3xl mx-auto">
    @foreach ($activities as $activity)
        <div class="relative">
            <img class="rounded-md" src="{{ asset('assets/images/welcome/activities/' . $activity['image']) }}" alt="{{ $activity['title'] }}" class="w-full h-auto" />
            <p class="absolute bottom-4 w-full text-center text-xl font-bold text-white uppercase">{{ $activity['title'] }}</p>
        </div>
    @endforeach
</div>
