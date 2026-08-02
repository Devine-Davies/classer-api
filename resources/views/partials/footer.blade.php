@php
    // Update these as needed
    $logoSrc = Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-logo.svg'); // dummy logo
    $companyName = 'CLASSER';
    $tagline = 'Made in the UK, with a worldwide mindset';

    $footerColumns = [
        'Discover' => [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Our blog', 'href' => url('/blog')],
            ['label' => 'Our stories', 'href' => url('/stories')],
            ['label' => 'Our guides', 'href' => route('guides')],
        ],
        'Company' => [
            ['label' => 'About us', 'href' => route('about')],
            ['label' => 'Contact', 'href' => route('contact')],
            ['label' => 'Classer Share', 'href' => route('classer-share')],
            ['label' => 'Privacy Policy', 'href' => route('privacy-policy.localized', ['isoLanCode' => 'en-gb'])],
        ],
        'Resources' => [
            ['label' => 'App', 'href' => route('app.index')],
            ['label' => 'Download', 'href' => route('download')],
            ['label' => 'Action Camera Matcher', 'href' => route('acm.index')],
        ],
        'Follow us' => [
            ['label' => 'Instagram', 'href' => 'https://www.instagram.com/weareclassermedia/', 'icon' => 'instagram'],
            ['label' => 'Reddit', 'href' => 'https://www.reddit.com/r/ActionCam/', 'icon' => 'reddit'],
            ['label' => 'Discord', 'href' => 'https://discord.gg/JHVpgpB8', 'icon' => 'discord'],
        ],
    ];
@endphp

<footer>
    <div class="flex flex-col items-center gap-12 md:gap-8 lg:gap-24 md:flex-row lg:justify-between">

        {{-- Logo + brand --}}
        <div class="flex flex-col gap-3 items-center lg:items-start min-w-[185px]">
            <!-- wrap -->
            <a href="{{ url('/') }}">
                <div class="flex flex-col items-center gap-3">
                    <img src="{{ $logoSrc }}" alt="{{ $companyName }} logo" class="h-10 w-10 object-contain">
                    <img class="py-2 px-4 w-40" src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-text.svg') }}"
                        alt="{{ $companyName }}" />
                </div>
            </a>
        </div>

        {{-- Link columns --}}
        <div class="flex-1 grid grid-cols-2 gap-12 grid-cols-2 md:grid-cols-4 w-full justify-center">
            @foreach ($footerColumns as $heading => $links)
                <div class="flex flex-col gap-3 text-center md:text-left">  
                    <h3 class="font-bold tracking-wide text-gray-500 uppercase mb-2">
                        {{ $heading }}
                    </h3>

                    <ul class="space-y-2" style="list-style-type: none; padding-left: 0;">
                        @foreach ($links as $link)
                            <li>
                                <a href="{{ $link['href'] }}"
                                    class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 hover:underline">
                                    {{-- Optional social icons --}}
                                    @isset($link['icon'])
                                        <span
                                            class="text-gray-500 fill-gray-500 w-5 h-5 flex items-center justify-center hidden lg:inline-flex w-5 h-5">
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

                                    <span class="flex-1">{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>

    {{-- Bottom tagline --}}
    <div class="my-8 text-center text-sm font-bold text-gray-500 tracking-wide">
        {{ $tagline }}
    </div>
</footer>
