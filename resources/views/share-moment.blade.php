<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.shared.naviagtion')

    <article class="max-w-3xl mx-auto h-full flex flex-col justify-center items-center">
        <video
            class="w-full h-auto rounded-lg"
            controls
            poster="{{ $thumbnailSrc }}"
            preload="auto"
            @if ($videoSrc) src="{{ $videoSrc }}" @endif >
            <source src="{{ $videoSrc }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </article>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
