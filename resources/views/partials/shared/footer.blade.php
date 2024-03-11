@php
    $reditIcon = asset('/assets/images/jam-icons/icons/reddit.svg');
    $instagramIcon = asset('/assets/images/jam-icons/icons/instagram.svg');
    $classerLogo = asset('/assets/images/brand/classer-logo.svg');
    $classerText = asset('/assets/images/brand/classer-text.svg');
@endphp

<footer class="bg-off-white w-full px-4">
    <div class="max-w-7xl m-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 py-8">
        <div class="flex items-center justify-center">
            <img class="py-2 w-14" src="{{ asset('/assets/images/brand/classer-logo.svg') }}" alt="Classer Symbol Logo" />
            <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/brand/classer-text.svg') }}" alt="Classer Text Logo" />
        </div>
        <div class="text-center mt-2 flex flex-col items-center justify-center gap-3">
            <a aria-label="Contact Email" href="mailto:info@classermedia.com"
                class="ml-4 hover:underline">
                info@classermedia.com
            </a>

            <p>© 2024 Classer Media, Inc. Made in the UK, with a worldwide mindset</p>
        </div>
        <div
            class="text-center mt-8 md:mt-0 md:text-right flex items-center justify-center gap-16 md:gap-4 md:col-start-2 lg:col-start-3">
            <a aria-label="Instagram" class= hover:underline"
                href="https://www.instagram.com/classermedia_" target="_blank">
                Instagram
            </a>
            <a aria-label="Reddit" class= hover:underline" href="https://www.reddit.com/r/classer"
                target="_blank">
                Reddit
            </a>
        </div>
    </div>
</footer>
