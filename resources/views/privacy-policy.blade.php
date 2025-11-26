<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')
    <article class="max-w-7xl mx-auto">
        <div class="markdown-body mb-8 lg:mb-12" >
            {!! $content !!}
        </div>
    </article>
    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
