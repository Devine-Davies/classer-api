
@php
    $steps = [
        [
            'label' => 'Connect to your Wi-Fi',
            'text' => 'Connect Classer Home to your router using the Ethernet cable included with your device, so everyone can access the footage from anywhere in the house.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step01.jpg'),
            'alt' => 'Classer Home connected to an external hard drive',
            'imagePosition' => 'left',
        ],
        [
            'label' => 'Plug it in',
            'text' => 'Connect Classer Home to power and leave it in one permanent place, ready whenever you want to use it.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step02.jpg'),
            'alt' => 'Classer Home organising footage into collections',
            'imagePosition' => 'right',
        ],
        [
            'label' => 'Connect your hard drive',
            'text' => 'Plug in the hard drive where your footage already lives. It stays in one place while everyone at home can access it through Classer.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step03.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'left',
        ],
        [
            'label' => 'Explore with the Classer app',
            'text' => 'Open the free Classer app to browse your footage visually, organise it into collections and rediscover moments you had forgotten.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step04.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'right',
        ],
        [
            'label' => 'Import footage from your camera',
            'text' => 'You can also connect your action camera directly to Classer Home and import new footage straight to your hard drive, without moving it or connecting it to your computer.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step05.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'left',
        ],
    ];
@endphp

<x-steps-showcase :steps="$steps" />