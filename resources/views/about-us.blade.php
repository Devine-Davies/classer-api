<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.shared.navigation')

    <section class="px-6 py-6 md:py-12">
        <div class="max-w-7xl mx-auto">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                {{-- Text column --}}
                <article class="space-y-6">
                    <header class="space-y-4">
                        <h1 class="text-brand-color text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight text-emerald-700">
                            A home for the<br>
                            <span class="">moments that matter</span>
                        </h1>
                    </header>

                    <p class="text-sm sm:text-base leading-relaxed text-slate-600 max-w-xl">
                        Classer began with a simple frustration, we were capturing incredible 
                        moments with our action cameras, but losing them just as quickly. 
                        Buried in messy folders, scattered across SD cards, or forgotten in 
                        cloud dumps, memories slowly stopped feeling meaningful, they just 
                        became files.
                    </p>

                    <p class="text-sm sm:text-base font-semibold leading-relaxed text-slate-600 max-w-xl">
                        We wanted to change that.
                    </p>

                    <p class="text-sm sm:text-base leading-relaxed text-slate-600 max-w-xl">
                        Classer was created by two people who love filming adventures with 
                        their action cameras, but love experiencing them even more. 
                        We wanted more intentional way to manage our footage. Something 
                        that didn’t turn every moment into “content,” but let the important 
                        ones shine.
                    </p>
                </article>

                {{-- Image column --}}
                <figure class="relative">
                    <div class="overflow-hidden rounded-3xl shadow-md">
                        {{-- Replace this with your real image or use asset() --}}
                        <img
                            src="{{ asset('/assets/images/about/founders.jpeg') }}"
                            alt="People enjoying an outdoor adventure"
                            class="w-full h-full object-cover">
                    </div>
                    <figcaption class="sr-only">
                        Two people smiling outdoors while biking.
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="bg-white px-6 py-6 md:py-12" aria-labelledby="our-values-title">
        <div class="max-w-7xl mx-auto">
            <header class="text-center max-w-2xl mx-auto mb-12">
                <h2 id="our-values-title" class="text-3xl sm:text-4xl font-semibold text-brand-color">
                    Our values
                </h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">
                    We believe your memories deserve a proper home, not an overwhelming archive.
                </p>
            </header>

            <div class="grid gap-8 md:grid-cols-3">
                {{-- Card 1 --}}
                <article class="flex flex-col overflow-hidden bg-white">
                    <figure class="aspect-[4/3] w-full overflow-hidden">
                        <img
                            src="{{ asset('/assets/images/about/title-1.jpg') }}"
                            alt="Printed memories displayed together, symbolising human connection"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </figure>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Connection
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Memories mean nothing unless they bring people together. We design
                            Classer so we can relive experiences with the people who made them
                            special, not just store footage, but revisit the stories behind it.
                        </p>
                    </div>
                </article>

                {{-- Card 2 --}}
                <article class="flex flex-col overflow-hidden bg-white">
                    <figure class="aspect-[4/3] w-full overflow-hidden">
                        <img
                            src="{{ asset('/assets/images/about/title-2.jpg') }}"
                            alt="People relaxing by a waterfall, representing a lighter footprint"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </figure>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Sustainability
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Storing everything has a cost: to your mind, your hard drive,
                            and the planet. We help keep only the meaningful moments and
                            reduce digital waste, creating a healthier, lighter relationship
                            with your content.
                        </p>
                    </div>
                </article>

                {{-- Card 3 --}}
                <article class="flex flex-col overflow-hidden bg-white">
                    <figure class="aspect-[4/3] w-full overflow-hidden">
                        <img
                            src="{{ asset('/assets/images/about/title-3.jpg') }}"
                            alt="Person raising arms at sunrise, representing joy"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </figure>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Joy
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Technology should feel simple, calm, and delightful. Classer is
                            built to make organising feel satisfying instead of stressful,
                            so you can spend more time doing what you love.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="px-6 py-6 md:py-12" >
        <div class="mx-auto max-w-7xl">
            @include('partials.home.micro-movies')
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>