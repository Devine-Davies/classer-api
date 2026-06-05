<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer Home - We record everything. We remember almost nothing.</title>
    @include('partials.shared.meta')
    @vite('resources/css/app.css')
    @vite('resources/css/markdown/main.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        h1, h2, h3, p, a, .gallery-title { font-family: 'Hanken Grotesk', sans-serif; }
        .label { font-family: 'Hanken Grotesk', monospace; }
    </style>
</head>

<body class="antialiased" style="font-family: 'Hanken Grotesk', sans-serif;">
    @include('partials.shared.navigation')

    {{-- Hero --}}
    <section class="relative w-full overflow-hidden bg-neutral-900">
        @include('classer-home-2.partials.hero')
    </section>

    {{-- Problem / hard drives --}}
    <section class="bg-white py-16 md:py-24">
        @include('classer-home-2.partials.problem')
    </section>

    {{-- We built the home for your memories --}}
    <section class="bg-white pb-16 md:pb-24">
        @include('classer-home-2.partials.home-banner')
    </section>

    {{-- Vendor logos (partial ships its own heading) --}}
    <section class="bg-white pb-12 md:pb-16">
        @include('partials.home.vendors')
    </section>

    {{-- Tabs section: The place where your adventures come back to life --}}
    <section class="bg-white py-16 md:py-24">
        @include('classer-home-2.partials.tabs-showcase')
    </section>

    {{-- Find the moments — reuse mosaic gallery (mosaic ships its own heading) --}}
    <section class="bg-white pt-8 pb-16 md:pb-24">
        @include('classer-home.partials.gallery-mosaic')
    </section>

    {{-- Built for the long run --}}
    <section class="bg-white py-16 md:py-24">
        @include('classer-home-2.partials.long-run')
    </section>

    {{-- Early access --}}
    <section class="px-8 py-16 md:py-24" style="background-color: #f5f7f6;">
        @include('classer-home.partials.kick-starter')
    </section>

    {{-- Testimonials / Insights --}}
    <section class="bg-white py-16 md:py-24">
        @include('classer-home-2.partials.insights')
    </section>

    {{-- FAQ --}}
    <section class="bg-white py-16 md:py-24">
        @include('partials.home.f-a-q', ['faqs' => [
            [
                'q' => 'Is it for mobile?',
                'a' => 'Not yet — Classer Home is currently focused on desktop and the home device.',
                'category' => 'Mobile Features',
            ],
            [
                'q' => 'Can I cut and trim my videos?',
                'a' => 'Yes, basic editing features are part of the roadmap.',
                'category' => 'Editing Features',
            ],
            [
                'q' => 'Is this a cloud service?',
                'a' => 'No, Classer Home is local-first. Your footage stays in your home.',
                'category' => 'Cloud & Sync',
            ],
            [
                'q' => 'Does Classer use my directory from my folder file?',
                'a' => 'Yes — Classer reads your existing folder structure and organises on top of it without moving your originals.',
                'category' => 'File Management',
            ],
            [
                'q' => 'Does it work with all action cameras?',
                'a' => 'It supports all major action cameras and standard video formats.',
                'category' => 'Compatibility',
            ],
            [
                'q' => 'I would like to contact the team, how do I do it?',
                'a' => 'Reach out via the contact page or join the early access list to chat with the team directly.',
                'category' => 'Support',
            ],
            [
                'q' => 'How to turn on my GPS on my GoPro?',
                'a' => 'Open your GoPro settings, go to Preferences > Regional > GPS, and switch it on.',
                'category' => 'Guides',
            ],
        ]])
    </section>

    @include('partials.shared.footer')
</body>

</html>
