<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer Home - The home device that looks after your adventures</title>
    @include('partials.shared.meta')
    @vite('resources/css/app.css')
    @vite('resources/css/markdown/main.css')


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet"></head>

    <style>
        /* Custom font styles */
        body {
            font-family: 'Hanken Grotesk', sans-serif;
        }

        h1, h2, h3 {
            font-family: 'Hanken Grotesk', sans-serif;
        }

        p, a, .gallery-title {
            font-family: 'Hanken Grotesk', sans-serif;
        }

        .label {
            font-family: 'Hanken Grotesk', monospace;
        }
    </style>
</head>

<body class="antialiased" style="font-family: 'Hanken Grotesk', sans-serif;">

    <section class="relative w-full h-[35svh] md:h-[45svh] overflow-hidden bg-neutral-800">
        @include('classer-home.partials.hero')
    </section>

    <section class="bg-white py-16 md:py-24">
        @include('classer-home.partials.problem')
    </section>

    <section class="bg-gray-50 py-16 md:py-24 overflow-hidden">
        @include('classer-home.partials.how-it-works')
    </section>

    <section class="bg-white">
        @include('classer-home.partials.gallery-mosaic')
    </section>

    <section class="px-8 py-16 md:py-24" style="background-color: #f5f7f6;">
        @include('classer-home.partials.kick-starter')
    </section>

    <section class="bg-white">
        @include('classer-home.partials.trusted')
    </section>

    <section class="bg-white py-16 md:py-24">
        @include('partials.home.f-a-q', ['faqs' => [
        [
            'q' => 'Is Classer Home available now?',
            'a' => 'Not yet, we\'re currently prototyping. Kickstarter is planned for 2026.',
            'category' => 'Availability',
        ],
        [
            'q' => 'Is this cloud-based?',
            'a' => 'No, Classer Home is a local-first device. Your footage stays in your home.',
            'category' => 'Cloud & Sync',
        ],
        [
            'q' => 'Do I need a subscription?',
            'a' => 'No, we\'re designing it so the core experience works without a subscription.',
            'category' => 'Pricing',
        ],
        [
            'q' => 'What cameras will it support?',
            'a' => 'It supports all major cameras and video formats. Our main focus is action cameras at the moment.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'Can I follow progress?',
            'a' => 'Yes, waitlist members get build updates and launch details.',
            'category' => 'Updates',
        ],
        [
            'q' => 'Where is my footage stored?',
            'a' => 'Classer Home connects to your external hard drive, keeping your footage safely stored in your hard drive.',
            'category' => 'Storage',
        ],
        [
            'q' => 'Which systems does it support?',
            'a' => 'Mac and Windows.',
            'category' => 'Platforms & Devices',
        ],
        [
            'q' => 'Why a device?',
            'a' => 'External hard drives are powerful, but they were never designed to organise memories. Classer Home removes the manual steps. No dragging files. No renaming folders. No sorting later. Just plug in and let it organise.',
            'category' => 'Product',
        ],
    ]])
    </section>

    @include('partials.shared.footer')
</body>

</html>
