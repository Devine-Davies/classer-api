<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="antialiased">
    @include('partials.navigation')
    <article class="max-w-7xl mx-auto">
        <div class="markdown-body mb-8 lg:mb-12">
            {!! $content !!}
        </div>
    </article>
    @include('partials.footer')
    @include('partials.modals')
</body>

</html>
