<!-- @php
    $reditIcon = asset('/assets/images/jam-icons/icons/reddit.svg');
    $instagramIcon = asset('/assets/images/jam-icons/icons/instagram.svg');
    $classerLogo = asset('/assets/images/brand/classer-logo.svg');
    $classerText = asset('/assets/images/brand/classer-text.svg');
@endphp

<footer class="bg-off-white w-full px-4">
    <div class="max-w-7xl m-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 py-8">
        <div class="flex items-center justify-center md:justify-start">
            <img class="py-2 w-14" src="{{ asset('/assets/images/brand/classer-logo.svg') }}" alt="Classer Symbol Logo" />
            <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}"
                alt="Classer Text Logo" />
        </div>
        <div class="text-center mt-2 flex flex-col items-center justify-center gap-3">
            <p>© 2025 Classer Media. <br /><a aria-label="Contact Email" href="mailto:contact@classermedia.com"
                    class="hover:underline">
                    contact@classermedia.com
                </a><br /> Living adventures every day. Made in Wales</p>
        </div>
        <div
            class="text-center mt-8 md:mt-0 md:text-right flex items-center justify-center md:justify-end gap-16 md:gap-4 md:col-start-2 lg:col-start-3">
            <a aria-label="Instagram" class=hover:underline" href="https://www.instagram.com/weareclassermedia/"
                target="_blank">
                Instagram
            </a>
            <a aria-label="Reddit" class=hover:underline" href="https://www.reddit.com/r/ActionCam/" target="_blank">
                Reddit
            </a>
        </div>
    </div>
</footer> -->


{{-- resources/views/components/site-footer.blade.php --}}

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
@endphp

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