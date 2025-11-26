@php
$logosImgPaths = [
'akaso' => asset('/assets/images/welcome/logos/akaso.png'),
'sjcam' => asset('/assets/images/welcome/logos/sjcam.png'),
'dji' => asset('/assets/images/welcome/logos/dji.png'),
'go-pro' => asset('/assets/images/welcome/logos/go-pro.png'),
'insta360' => asset('/assets/images/welcome/logos/insta360.png'),
];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Action Camera Matcher</title>

    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
    @vite('resources/views/action-camera-matcher/index/index.css')
    @vite('resources/views/action-camera-matcher/index/index.js')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')

    <section class="bg-white">
        <div class="relative px-3 md:pt-12 mx-auto lg:py-32 md:px-8 xl:px-20 md:max-w-full">
            <div class="max-w-5xl mx-auto">
                <div class="mb-16 lg:max-w-lg lg:mb-0">
                    <div class="max-w-xl mb-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-brand-color mb-6 tracking-wide">
                            Find the action camera that suits your needs
                        </h2>
                        <p class="text-base text-gray-700 md:text-lg">
                            Answer a few questions and we'll recommend the best action camera for you.
                        </p>
                    </div>

                    <div class="flex items-center">
                        <a aria-label="Download Classer" href="/action-camera-matcher/questions"
                            class="btn text-lg">
                            Start here
                        </a>
                    </div>

                    <div class="hidden grid-cols-3 md:grid-cols-5 xl:grid">
                        @foreach ($logosImgPaths as $logoName => $logoImgPath)
                        <div class="h-16 flex align-center justify-center">
                            <img class="m-auto w-6/12" src="{{ $logoImgPath }}"
                                alt="{{ ucfirst($logoName) }} logo" />
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center h-full lg:w-2/3 xl:w-1/2 lg:absolute lg:justify-start lg:bottom-0 lg:right-0 lg:items-end">
                <img src="{{ asset('/assets/images/action-camera-matcher/cameras@2x.png') }}"
                    class="object-cover -mt-20 md:-mt-28 object-top w-full h-64 max-w-xl lg:ml-64 xl:ml-8 lg:-mb-24 lg:h-auto"
                    alt="" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-6 md:py-12">
        @include('partials.shared.posts', [
            'title' => 'Blog Posts',
            'cards' => $posts,
            'masonryType' => 'blog-posts',
        ])
        <div class="text-center mt-8 underline">
            <a href="/blog" class="text-center text-underline">View all</a>
        </div>
    </section>

    <section id="join-our-community-section" class="bg-off-white">
        <div>
            @include('partials.home.join-our-community')
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>