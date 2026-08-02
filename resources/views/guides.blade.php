<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.navigation')
    @include('partials.modals')

    <section id="guides-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.guides')
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
