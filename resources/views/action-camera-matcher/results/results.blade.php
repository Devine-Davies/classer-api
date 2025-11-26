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
    @vite('resources/views/action-camera-matcher/results/results.css')
    @vite('resources/views/action-camera-matcher/results/results.js')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')


    <pre></pre>

    <section class="bg-white">
        <div class="relative px-3 md:pt-4 mx-auto lg:py-8 md:px-8 xl:px-20 md:max-w-full">
            <div data-results class="acm-results-pane flex flex-col h-full">
                <div class="relative w-full mb-6 text-center">
                    <h1 class="text-xl lg:text-4xl font-bold text-brand-color">
                        We recommend you
                    </h1>
                </div>
                <ul>
                    @foreach ($recommendations as $recommendation)

                    <li class="recommendation-item py-8 {{ $recommendation['key'] }}">
                        <div class="flex space-between relative flex-col md:flex-row md:items-center md:justify-between gap-6 md:gap-0">
                            <img class="absolute top-0 left-0 w-12 h-12 rounded-full" src="{{ asset('/assets/images/action-camera-matcher/rankings/' . $recommendation['recommendation_key'] . '.svg') }}">

                            <img class="object-contain w-full max-w-[175px] h-auto mx-auto"
                                src="{{ $recommendation['image'] }}" alt="glasses photo">

                            <div class="flex flex-col flex-1 justify-center w-full mx-4 lg:mx-0 ">
                                <h3 class="text-xl font-bold text-brand-color pl-2 mb-1">
                                    {{ $recommendation['title'] }}
                                </h3>

                                @if ($recommendation['benefits'])
                                    <ul>
                                        @foreach ($recommendation['benefits'] as $benefit)
                                        <li class="pl-4 py-1 flex items-center text-sm">
                                            <span class="pr-1"  >@icon(tick)</span>
                                            <span>{{ $benefit }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="flex flex-col items-center justify-center gap-1">
                                <a 
                                    target="_new" 
                                    href="{{ $recommendation['affiliateLink'] }}"
                                    class="btn  {{ $recommendation['key'] }}">Buy Camera</a>
                                <p class="text-gray-500 text-xs">{{ $recommendation['recommendation'] }}</p>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>