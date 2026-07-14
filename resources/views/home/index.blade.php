<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer Home - We record everything. We remember almost nothing.</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
</head>

<body class="antialiased">
    @include('partials.navigation', ['state' => 'transparent'])

    {{-- Hero --}}
    <section class="relative -mt-[88px] w-full overflow-hidden bg-neutral-900">
        @include('home.partials.hero')
    </section>

    {{-- Problem / hard drives --}}
    <section>
        <div class="w-full px-4 md:px-6 pt-12 pb-24">    
            <div class="mx-auto text-center">
                @include('home.partials.adventures-disappear')
            </div>
        </div>
    </section>

    {{-- We built the home for your memories --}}
    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('home.partials.home-banner')
            </div>
        </div>
    </section>

    {{-- Vendor logos (partial ships its own heading) --}}
    <section>
        <div class="mx-auto w-full max-w-7xl px-6 mt-8 mb-5">
            @include('partials.vendors')
        </div>
    </section>

    {{-- Tabs section: The place where your adventures come back to life --}}
    <section>
        <div class="mx-auto bg-[#fafafa] overflow-hidden">
            <div class="bg-classer-cream w-full max-w-7xl m-auto px-4 md:px-6 py-12">
                @include('home.partials.tabs-showcase')
            </div>
        </div>
    </section>

    {{-- Built for the long run --}}
    <section>
        <div class="mx-auto">
            <div class="bg-classer-cream w-full m-auto">
                <x-image-feature
                    :imageSrc="Storage::disk('s3')->url('classermedia.com/assets/images/classer-2/device-showcase.jpg')"
                    imageAlt="Classer app being used on an iPad"
                    title="Give your old footage somewhere to live"
                    description="Your hard drives are full of moments you still care about. Classer helps you bring them out of storage and back into everyday life."
                    buttonLabel="How it works"
                    :buttonUrl="url('/products/classer-home')"
                />
            </div>        
        </div>
    </section>

    {{-- Card Carousel --}}
    <section>
        <x-card-carousel
            class="mx-auto py-8 md:py-12"
            title="See our community stories"
            intro="Sharing is learning."
            :cards="$stories"
        />
    </section>

    {{-- Vendor logos (partial ships its own heading) --}}
    <section>
        @include('partials.banner')
    </section>

    {{-- FAQ --}}
    <section class="mt-8 md:mt-12">
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                <div class="w-full">
                    @include('partials.f-a-q', ['faqs' => $faqs])
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>

</html>
