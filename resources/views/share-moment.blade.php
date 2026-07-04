<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.navigation')

    <article class="max-w-3xl mx-auto h-full flex flex-col justify-center items-center">
        <video class="w-full h-auto rounded-lg" controls poster="{{ $thumbnailSrc }}" preload="auto"
            @if ($videoSrc) src="{{ $videoSrc }}" @endif>
            <source src="{{ $videoSrc }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </article>

    @include('partials.footer')
    @include('partials.modals')
</body>

</html>
