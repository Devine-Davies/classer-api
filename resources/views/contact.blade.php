<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Contact</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body>
    @include('partials.navigation')
    @include('partials.modals')

    <section class="max-w-7xl m-auto p-3 md:justify-between">
        <article class="space-y-6">
            <header class="space-y-4">
                <h1
                    class="text-brand-color text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight text-emerald-700">
                    Get in touch
                </h1>
            </header>

            <p class="text-sm sm:text-base leading-relaxed text-slate-600 max-w-xl">
                For support inquiries or help using Classer, send us a message at
                <a href="mailto:contact@classermedia.com" class="text-brand-color underline">contact@classermedia.com</a>.
            </p>
        </article>
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
