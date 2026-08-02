@php
    $trialCode = isset($_GET['trial-code']) ? $_GET['trial-code'] : '';
    $trialDownloadUrl = '/downloads/sample.pdf';
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {!! $title !!}</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="antialiased" trial-code="{!! $trialCode !!}">
    @include('partials.navigation')

    <div class="my-8 md:my-12"></div>

    <article>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-4xl">
                <div class="markdown-body">
                    {!! $content !!}
                </div>
            </div>
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

    @include('partials.modals')
</body>

</html>
