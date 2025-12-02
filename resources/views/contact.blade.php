<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Contact</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body>
    @include('partials.shared.navigation')

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

    <div class="bottom-0 mt-8 w-full md:fixed">
        @include('partials.shared.footer')
    </div>

    @include('partials.shared.modals')
</body>

</html>
