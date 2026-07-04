<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer Home - The home device that looks after your adventures</title>
    @include('partials.meta')
    @vite('resources/css/app.css')
    @vite('resources/css/markdown/main.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet"></head>

    <style>
        .label {
            font-family: 'Hanken Grotesk', monospace;
        }
    </style>
</head>

<body class="antialiased">
    @include('partials.navigation')

    <section class="relative w-full h-[35svh] md:h-[45svh] overflow-hidden bg-neutral-800 md:rounded-b-[40px]">
        @include('classer-home.partials.hero')
    </section>

    <section class="relative sm:-mt-8 md:-mt-20 z-30 rounded-[40px] py-16 md:py-24 max-w-7xl mx-auto" style="background-color: #f5f5f5;">
        @include('classer-home.partials.app-showcase')
    </section>

    <section class="bg-white py-16 md:py-24">
        @include('classer-home.partials.problem')
    </section>

    <section class="bg-gray-50 py-16 md:py-24 overflow-hidden">
        @include('classer-home.partials.how-it-works')

        <div class="mt-24 my-8">
            @include('partials.vendors')
        </div>
    </section>

    <section class="bg-white py-16 md:py-24 overflow-hidden">
        @include('classer-home.partials.showcase-2')
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
        @include('partials.f-a-q', ['faqs' => $faqs])
    </section>

    @include('partials.footer')
</body>

</html>
