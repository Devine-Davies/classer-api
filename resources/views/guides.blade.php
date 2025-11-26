<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.shared.navigation')

    <section id="guides-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.guides')
        </div>
    </section>

    <section id="how-it-works-section" class="bg-off-white" >
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.how-it-works')
            <div class="mt-12 my-8">
            @include('partials.home.vendors')
            </div>
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>

