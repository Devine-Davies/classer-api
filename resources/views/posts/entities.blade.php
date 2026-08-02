<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - {{ $title }}</title>
    @include('partials.meta')
</head>

<body class="antialiased">
    @include('partials.navigation')
    @include('partials.modals')

    <div class="my-8 md:my-12"></div>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('partials.posts', [
                    'title' => $title,
                    'masonryType' => 'offset-y',
                ])
            </div>
        </div>
    </section>

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
