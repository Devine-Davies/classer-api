<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {{ $title }}</title>
    @include('partials.meta')
</head>

<body class="antialiased">
    @include('partials.navigation')

    <section class="mx-auto max-w-7xl px-6 py-6 md:py-12">
        @include('partials.posts', [
            'title' => $title,
            'masonryType' => 'offset-y',
        ])
    </section>

    @include('partials.footer')
    @include('partials.modals')
</body>

</html>
