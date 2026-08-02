<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="antialiased">
    @include('partials.navigation')
    @include('partials.modals')

    <article class="max-w-7xl mx-auto">
        <div class="markdown-body mb-8 lg:mb-12">
            {!! $content !!}
        </div>
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
