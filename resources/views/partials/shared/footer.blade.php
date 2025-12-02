@php
    // Update these as needed
    $logoSrc = asset('/assets/images/brand/classer-logo.svg'); // dummy logo
    $companyName = 'CLASSER';
    $tagline = 'Made in the UK, with a worldwide mindset';

    $footerColumns = [
        'Discover' => [
            ['label' => 'Our blog', 'href' => url('/blog')],
            ['label' => 'Our stories', 'href' => url('/stories')],
            ['label' => 'Our guides', 'href' => url('/guides')],
        ],
        'Company' => [
            ['label' => 'About us', 'href' => url('/about')],
            ['label' => 'Contact', 'href' => url('/contact')],
            ['label' => 'Privacy Policy','href' => url('/privacy-policy/en-gb')],
        ],
        'Follow us' => [
            ['label' => 'Instagram', 'href' => 'https://www.instagram.com/weareclassermedia/', 'icon' => 'instagram'],
            ['label' => 'Reddit', 'href' => 'https://www.reddit.com/r/ActionCam/', 'icon' => 'reddit'],
            ['label' => 'Discord', 'href' => 'https://discord.gg/JHVpgpB8', 'icon' => 'discord'],
        ],
        'App' => [
            ['label' => 'Download', 'href' => url('/download')]
        ],
    ];


    // Check if current path matches special paths (including dynamic segments)
    $currentPath = request()->path();
    
    $specialPaths = ['action-camera-matcher', 'blog', 'stories', 'guides'];
    $isSpecialPath = collect($specialPaths)->contains(fn($path) => 
        str_starts_with($currentPath, $path)
    );
@endphp

@if($isSpecialPath)
    {{-- Left sticky ad (only on 1440px+) --}}
    <div id="sticky-ad-left" class="hidden">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5548191229275160"
            crossorigin="anonymous"></script>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-5548191229275160"
            data-ad-slot="2724610538"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>

    {{-- Right sticky ad (only on 1440px+) --}}
    <div id="sticky-ad-right" class="hidden">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5548191229275160"
            crossorigin="anonymous"></script>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-5548191229275160"
            data-ad-slot="2724610538"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>

    {{-- Bottom sticky ad (mobile/tablet) --}}
    <div id="sticky-ad-bottom">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5548191229275160"
            crossorigin="anonymous"></script>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-5548191229275160"
            data-ad-slot="2724610538"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>

    <style>
        /* Bottom sticky ad (default for mobile/tablet) */
        #sticky-ad-bottom {
            position: sticky;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            text-align: center;
            padding: 5px 0;
            background-color: #fff;
            border-top: 1px solid #eee;
        }

        #sticky-ad-left,
        #sticky-ad-right {
            display: none;
        }

        /* Show side ads on screens 1440px+ */
        @media (min-width: 1440px) {
            /* Hide bottom ad on large screens */
            #sticky-ad-bottom {
                display: none;
            }

            /* Left sticky ad */
            #sticky-ad-left {
                display: block;
                position: fixed;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 160px;
                z-index: 1000;
                padding: 10px;
                background-color: #fff;
                border-right: 1px solid #eee;
            }

            /* Right sticky ad */
            #sticky-ad-right {
                display: block;
                position: fixed;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 160px;
                z-index: 1000;
                padding: 10px;
                background-color: #fff;
                border-left: 1px solid #eee;
            }
        }
    </style>
@endif

<footer class="border-t  w-full bg-white text-gray-600 text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col items-center gap-8 md:flex-row lg:justify-between">

            {{-- Logo + brand --}}
            <div class="flex flex-col gap-3 items-center lg:items-start min-w-[160px] flex-1">
                <!-- wrap -->
                <a href="{{ url('/') }}">
                <div class="flex flex-col items-center gap-3">
                    <img
                        src="{{ $logoSrc }}"
                        alt="{{ $companyName }} logo"
                        class="h-10 w-10 object-contain">
                    <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}"
                            alt="{{ $companyName }}" />
                </div>
                </a>
            </div>

            {{-- Link columns --}}
            <div class="flex-1 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 flex-[2] lg:flex-[3]">
                @foreach($footerColumns as $heading => $links)
                <div>
                    <h3 class="text-xs font-bold tracking-wide text-gray-500 uppercase mb-3">
                        {{ $heading }}
                    </h3>

                    <ul class="space-y-2 list-none"> {{-- removed bullets --}}
                        @foreach($links as $link)
                        <li>
                            <a
                                href="{{ $link['href'] }}"
                                class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 hover:underline">
                                {{-- Optional social icons --}}
                                @isset($link['icon'])
                                    <span class="text-gray-500 fill-gray-500 w-5 h-5 flex items-center justify-center">
                                        @switch($link['icon'])
                                        @case('instagram')
                                            @icon(instagram)
                                        @break
                                        
                                        @case('reddit')
                                        <span class="relative -left-[1px]">
                                            @icon(reddit)
                                        </span>
                                        @break
                                        
                                        @case('discord')
                                            @icon(discord)
                                        @break
                                        @endswitch
                                    </span>
                                @endisset

                                <span class="flex-1" >{{ $link['label'] }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>

                </div>
                @endforeach
            </div>

        </div>

        {{-- Bottom tagline --}}
        <div class="mt-8 text-xs text-gray-500">
            {{ $tagline }}
        </div>
    </div>
</footer>