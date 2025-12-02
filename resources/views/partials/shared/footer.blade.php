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


    // get action-camera-matcher from the url http://localhost/action-camera-matcher/questions , make this a list of paths if needed, if the path matches set a bool ture
    $currentPath = request()->path();
    $isSpecialPath = in_array($currentPath, ['action-camera-matcher', 'action-camera-matcher/questions', 'action-camera-matcher/results', 'blog', 'stories', 'guides']);    
@endphp

@if($isSpecialPath)
    <div class="sticky bottom-0 z-50 w-full bg-white border-t pt-2 pb-4 flex justify-center">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5548191229275160"
            crossorigin="anonymous"></script>
        <!-- Content Ad – Matcher / Stories / Blog -->
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