@php
    $trialCode = isset($_GET['trial-code']) ? $_GET['trial-code'] : '';
    $trialDownloadUrl = '/downloads/sample.pdf';
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {!! $title !!}</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased" trial-code="{!! $trialCode !!}">
    @include('partials.shared.naviagtion')

    <article class="w-full max-w-7xl mx-auto my-8">
        <div class="markdown-body" >
            {!! $content !!}
        </div>
    </article>
   
    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
