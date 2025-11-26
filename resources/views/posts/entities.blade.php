<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {{ $title }}</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')

    <section class="mx-auto max-w-7xl px-6 py-6 md:py-12">
        @include('partials.shared.posts', [
            'title' => $title,
            'masonryType' => 'offset-y',
        ])
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
