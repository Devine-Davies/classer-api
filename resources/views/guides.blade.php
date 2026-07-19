<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.navigation')

    <section id="guides-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.guides')
        </div>
    </section>

    @include('partials.footer')
    @include('partials.modals')
</body>

</html>
