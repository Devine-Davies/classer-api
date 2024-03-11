@php
    $trialCode = isset($_GET['trial-code']) ? $_GET['trial-code'] : '';
    $trialDownloadUrl = '/downloads/sample.pdf';
@endphp
<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - The essential accessory for your action camera & drones</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased" trial-code="<?php echo $trialCode; ?>">
    @include('partials.shared.naviagtion')

    <section id="hero-section">
        <div class="hero-bg" >
            @include('partials.home.hero')
        </div>
    </section>

    <section id="features-section">
        <div class="mx-auto w-full max-w-7xl mt-16 p-4 sm:p-8 overflow-hidden">
            @include('partials.home.features')
        </div>
    </section>

    <section id="vendors-section">
        <div class="mx-auto max-w-7xl px-2 my-4 md:my-18 lg:my-24">
            @include('partials.home.vendors')
        </div>
    </section>

    <section id="how-it-works-section" class="bg-off-white">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.how-it-works')
        </div>
    </section>

    <section id="not-just-a-tool-section" class="hidden md:block">
        @include('partials.home.not-just-a-tool')
    </section>

    <section id="made-for-all-section" class="bg-badge">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.made-for-all')
        </div>
    </section>

    <section id="micro-movies-section" class="bg-off-white" >
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.micro-movies')
        </div>
    </section>

    <section id="pricing-models-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.pricing-models')
        </div>
    </section>

    <section id="environmental-section">
        <div>
            @include('partials.home.environmental')
        </div>
    </section>

    <section id="insights-section" class="bg-off-white">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.insights')
        </div>
    </section>

    <section id="our-stories-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.our-stories')
        </div>
    </section>

    <section id="join-our-community-section" class="bg-off-white">
        <div>
            @include('partials.home.join-our-community')
        </div>
    </section>

    <section id="f-a-q-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.f-a-q')
        </div>
    </section>

    <section id="guides-section" class="bg-off-white">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.guides')
        </div>
    </section>

    <section id="banner-section">
        @include('partials.home.banner')
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
