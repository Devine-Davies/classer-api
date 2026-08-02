@php
    $buildYoutubeEmbedUrl = static function (string $url): string {
        $parts = parse_url($url);

        if (!is_array($parts)) {
            return $url;
        }

        $host = $parts['host'] ?? '';
        $videoId = '';

        if ($host === 'youtu.be') {
            $videoId = trim($parts['path'] ?? '', '/');
        } else {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $query['v'] ?? '';
        }

        if ($videoId === '') {
            return $url;
        }

        return sprintf(
            'https://www.youtube-nocookie.com/embed/%s?autoplay=1&rel=0&modestbranding=1',
            $videoId,
        );
    };

    $tutorialsItems = array_map(
        static fn(array $item): array => [
            ...$item,
            'embedUrl' => $buildYoutubeEmbedUrl($item['url']),
        ],
        $tutorialsItems,
    );
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Guides</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.navigation')
    @include('partials.modals')

    <section id="guides-section" x-data="guidesGallery(@js($tutorialsItems))">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            <header class="mb-8 text-center md:mb-12">
                <h1 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-3 text-absolute font-medium leading-[108.54%] text-center">Explore our guides</h1>
                <p class="mx-auto max-w-2xl text-base text-slate-600 md:text-lg">
                    Pick a guide from the list and watch it here without leaving the page.
                </p>
            </header>

            <div class="flex flex-col gap-5">
                <div class="relative max-w-md left-full translate-x-[-100%]" @keydown.escape.window="closeGuideMenu()">
                    <button
                        type="button"
                        @click="toggleGuideMenu()"
                        class="flex w-full items-center justify-between gap-4 rounded-md bg-white px-4 py-3 text-left shadow-sm ring-1 ring-[#0d4150]/10 transition hover:ring-[#0d4150]/20"
                        :aria-expanded="guideMenuOpen"
                        aria-controls="guide-menu"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0d4150]/60">Choose guide</p>
                            <p class="truncate text-base font-semibold text-slate-900" x-text="activeGuide ? activeGuide.label : 'Select a guide'"></p>
                        </div>

                        <svg
                            class="h-5 w-5 shrink-0 text-[#0d4150] transition-transform duration-200"
                            :class="guideMenuOpen ? 'rotate-180' : ''"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        id="guide-menu"
                        x-cloak
                        x-show="guideMenuOpen"
                        x-transition.opacity.scale.origin.top
                        @click.outside="closeGuideMenu()"
                        class="absolute left-0 right-0 top-[calc(100%+0.75rem)] z-20 max-h-[28rem] overflow-y-auto rounded-md bg-white p-3 shadow-2xl ring-1 ring-[#0d4150]/10"
                    >
                        <div class="space-y-3">
                            <template x-for="(guide, index) in guides" :key="guide.label">
                                <button
                                    type="button"
                                    @click="selectGuide(index)"
                                    class="group flex w-full items-center gap-4 border p-3 text-left transition duration-200"
                                    :class="activeIndex === index
                                        ? 'border-[#0d4150]/20 bg-[#f4efe9] shadow-sm'
                                        : 'border-transparent bg-white hover:border-[#0d4150]/10 hover:bg-slate-50'"
                                >
                                    <div class="relative h-20 w-28 shrink-0 overflow-hidden bg-slate-200">
                                        <img :src="guide.thumbnail" :alt="guide.alt" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="inline-flex size-10 items-center justify-center bg-white/90 text-[#0d4150] shadow-sm">▶</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-2 text-sm leading-6 text-slate-600" x-text="guide.description"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-md bg-[#09161a] shadow-2xl shadow-[#0d4150]/10 ring-1 ring-white/10">
                    <div class="aspect-video bg-black">
                        <iframe
                            x-show="activeGuide"
                            class="h-full w-full"
                            :src="activeGuide ? activeGuide.embedUrl : ''"
                            :title="activeGuide ? `${activeGuide.label} video` : 'Guide video'"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <!-- <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#0d4150]/60">Now playing</p> -->
                        <p class="max-w-2xl text-base leading-7 text-slate-700" x-text="activeGuide ? activeGuide.description : ''"></p>
                    </div>

                    <a
                        x-show="activeGuide"
                        :href="activeGuide ? activeGuide.url : '#'"
                        target="_blank"
                        rel="noreferrer"
                        class="inline-flex shrink-0 items-center justify-center rounded-md bg-[#0d4150] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#0a3340]"
                    >
                        Open on YouTube
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="my-8 md:my-12"></div>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('partials.footer')
            </div>
        </div>
    </section>

    <script>
        function guidesGallery(guides) {
            return {
                guides,
                activeIndex: 0,
                guideMenuOpen: false,
                get activeGuide() {
                    return this.guides[this.activeIndex] ?? null;
                },
                toggleGuideMenu() {
                    this.guideMenuOpen = !this.guideMenuOpen;
                },
                closeGuideMenu() {
                    this.guideMenuOpen = false;
                },
                selectGuide(index) {
                    if (index < 0 || index >= this.guides.length) {
                        return;
                    }

                    this.activeIndex = index;
                    this.closeGuideMenu();
                },
            };
        }
    </script>
</body>

</html>
