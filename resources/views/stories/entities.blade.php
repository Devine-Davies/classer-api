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

    <section id="our-stories-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.our-stories')
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
