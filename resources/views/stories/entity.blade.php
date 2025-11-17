@php
    $trialCode = isset($_GET['trial-code']) ? $_GET['trial-code'] : '';
    $trialDownloadUrl = '/downloads/sample.pdf';
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {!! $title !!}</title>
    @include('partials.shared.meta')
    {{-- @vite('resources/css/markdown/main.css') --}}
    @vite('resources/css/markdown/main.css')
</head>

<body class="antialiased" trial-code="{!! $trialCode !!}">
    @include('partials.shared.navigation')

    <article class="max-w-3xl mx-auto">
        <div class="m-8" >
            <div class="markdown-body" >
                {!! $content !!}
            </div>
        </div>
    </article>
   
    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
