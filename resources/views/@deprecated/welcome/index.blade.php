<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - The essential accessory for your action camera & drones</title>
    @include('partials.meta')
</head>

<body class="antialiased">
    @include('partials.navigation')

    <section id="hero-section">
        <div class="hero-bg" >
            @include('partials.home.hero')
        </div>
    </section>

    <section id="features-section" class="bg-off-white" >
        <div class="mx-auto w-full max-w-7xl p-8 pb-24 md:pt-16 overflow-hidden">
            @include('partials.home.features')
        </div>
    </section>

    <section id="made-for-all-section" class="bg-badge">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.made-for-all')
        </div>
    </section>

    <section id="how-it-works-section" class="bg-off-white" >
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.how-it-works')
            <div class="mt-12 my-8">
            @include('partials.vendors')
            </div>
        </div>
    </section>

    <section id="not-just-a-tool-section" class="hidden">
        @include('partials.home.not-just-a-tool')
    </section>

    <section id="pricing-models-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.pricing-models')
        </div>
    </section>

    <section id="insights-section">
        <div class="mx-auto max-w-8xl px-6 py-6 md:py-12">
            @include('partials.home.insights')
        </div>
    </section>

    <section id="environmental-section" class="hidden" >
        <div>
            @include('partials.home.environmental')
        </div>
    </section>

    <section id="join-our-community-section" class="bg-off-white">
        <div>
            @include('partials.home.join-our-community')
        </div>
    </section>

    <section id="f-a-q-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.f-a-q', ['faqs' => $faqs])
        </div>
    </section>

    <section id="banner-section">
        @include('partials.home.banner')
    </section>

    @include('partials.footer')
    @include('partials.modals')
</body>

</html>
