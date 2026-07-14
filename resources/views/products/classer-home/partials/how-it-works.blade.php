
@php
    $steps = [
        [
            'label' => 'Connect your devices',
            'text' => 'Classer Home imports your footage directly to your hard drive. Your hard drive stores the footage, while Classer Home makes it meaningful.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step01.jpg'),
            'alt' => 'Classer Home connected to an external hard drive',
            'imagePosition' => 'left',
        ],
        [
            'label' => 'Create collections',
            'text' => 'Classer Home turns raw files into meaningful collections, preserving GPS, telemetry and camera data. It works quietly in the background while you get on with your day.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step02.jpg'),
            'alt' => 'Classer Home organising footage into collections',
            'imagePosition' => 'right',
        ],
        [
            'label' => 'Browse all your adventures',
            'text' => 'Browse your adventures as collections, organised by trips, days, and activities in the app. Export or share them anytime. Compatible with Mac and Windows. For desktop and iPad/ tablets.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step03.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'left',
        ],
        [
            'label' => 'Browse all your adventures',
            'text' => 'Browse your adventures as collections, organised by trips, days, and activities in the app. Export or share them anytime.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step04.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'right',
        ],
        [
            'label' => 'Browse all your adventures',
            'text' => 'Browse your adventures as collections, organised by trips, days, and activities in the app. Export or share them anytime.',
            'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step05.jpg'),
            'alt' => 'Browse your adventures on Mac and Windows',
            'imagePosition' => 'left',
        ],
    ];
@endphp

<x-steps-showcase :steps="$steps" />