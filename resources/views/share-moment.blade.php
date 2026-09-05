<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Share</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.navigation')
    @include('partials.modals')

    <article class="max-w-3xl mx-auto h-full flex flex-col justify-center items-center">
        <video class="w-full h-auto rounded-lg" controls @if ($thumbnailSrc) poster="{{ $thumbnailSrc }}" @endif preload="metadata">
            @if ($videoSrc)
                <source src="{{ $videoSrc }}" type="video/mp4">
            @endif
            Your browser does not support the video tag.
        </video>
    </article>

    <div class="my-8 md:my-12"></div>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('partials.footer')
            </div>
        </div>
    </section>
</body>

</html>
