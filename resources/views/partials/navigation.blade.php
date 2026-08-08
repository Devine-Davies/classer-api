@php
    $currentPath = trim(parse_url(request()->getRequestUri(), PHP_URL_PATH), '/');
    $isHomePage = $currentPath === '';
    $navState = $state ?? ($isHomePage ? 'transparent' : 'default');
    $isTransparent = $navState === 'transparent';
    $navStartOffset = max(0, (int) ($startOffset ?? 0));
    $reserveSpace = (bool) ($reserveSpace ?? ! $isTransparent);
    $spacerBackground = $spacerBackground ?? null;
    $logoDefaultSrc = Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-logo.svg');
    $logoWhiteSrc = Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-logo-white.svg');
    $textDefaultSrc = Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-text.svg');
    $textWhiteSrc = Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-text-white.svg');
    $useWhiteBrand = $isTransparent;
    $currencyCatalogService = app(\App\Services\CurrencyCatalogService::class);

    $currencyOptions = $currencyCatalogService->getPublishedOptions();

    $activeCurrencyCode = strtolower((string) request()->session()->get('user_context.checkout.currency', 'gbp'));
    $activeCurrency = collect($currencyOptions)->firstWhere('code', $activeCurrencyCode) ?? $currencyOptions[0];

    $isActivePath = function (string $url) use ($currentPath): bool {
        $itemPath = trim(parse_url($url, PHP_URL_PATH), '/');

        return ($itemPath === '' && $currentPath === '')
            || ($itemPath !== '' && \Illuminate\Support\Str::startsWith($currentPath, $itemPath));
    };

    $navItems = [
        [
            'type' => 'link',
            'label' => 'Home',
            'url' => url(''),
        ],
        [
            'type' => 'dropdown',
            'id' => 'devices',
            'label' => 'Devices',
            'children' => [
                ['label' => 'Classer Home', 'url' => url('products/classer-home')],
            ],
        ],
        [
            'type' => 'dropdown',
            'id' => 'app',
            'label' => 'App',
            'children' => [
                ['label' => 'Features', 'url' => route('app')],
                ['label' => 'Download', 'url' => route('download')],
                ['label' => 'Guides', 'url' => route('guides')],
            ],
        ],
        [
            'type' => 'dropdown',
            'id' => 'discover',
            'label' => 'Discover',
            'children' => [
                ['label' => 'Action Camera Matcher', 'url' => url('/action-camera-matcher')],
                ['label' => 'Stories', 'url' => url('/stories')],
                ['label' => 'Blog', 'url' => url('/blog')],
            ],
        ]
    ];
@endphp

@once
    <style>
        /*
         * Single source of truth for the fixed nav height.
         * All spacer/overlap utilities consume this variable — update here to resize the header globally.
         */
        :root {
            --site-header-height: 88px;
        }

        /* Reserves the exact height of the fixed nav so page content starts below it. */
        .site-header-spacer {
            flex: 0 0 var(--site-header-height);
            min-height: var(--site-header-height);
            height: var(--site-header-height);
            flex-shrink: 0;
            background: var(--site-header-spacer-bg, transparent);
        }

        /*
         * Pull a full-bleed section (e.g. a hero) up behind the transparent nav.
         * Use on the first content element on transparent-nav pages instead of a spacer.
         */
        .nav-overlap {
            margin-top: calc(-1 * var(--site-header-height));
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        // Keep the spacer height aligned with the fixed header's rendered height.
        (function() {
            function syncSiteHeaderHeight() {
                var nav = document.getElementById('nav');

                if (!nav) {
                    return;
                }

                var navHeight = Math.ceil(nav.getBoundingClientRect().height);

                if (navHeight > 0) {
                    document.documentElement.style.setProperty('--site-header-height', navHeight + 'px');
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncSiteHeaderHeight, {
                    once: true,
                });
            } else {
                syncSiteHeaderHeight();
            }

            window.addEventListener('load', syncSiteHeaderHeight);
            window.addEventListener('resize', syncSiteHeaderHeight);
            window.addEventListener('orientationchange', syncSiteHeaderHeight);
        })();
    </script>
@endonce

<section
    id="nav"
    class="site-header {{ $isTransparent ? 'site-header--transparent' : 'site-header--default' }} w-full"
    style="--nav-start-offset: {{ $isTransparent ? $navStartOffset : 0 }}px;"
>
    <nav class="h-full w-full px-1 md:px-6">
        <div class="max-w-7xl m-auto flex items-center md:justify-between flex-col md:flex-row">
            <div class="flex justify-between items-center gap-4 w-full md:w-auto w-full py-3 md:py-5">
                <a href="{!! url('/') !!}" class="flex items-center">
                    <img
                        class="py-2 w-12 md:w-8"
                        src="{{ $useWhiteBrand ? $logoWhiteSrc : $logoDefaultSrc }}"
                        alt="Classer Symbol Logo"
                        data-nav-brand-logo
                        data-default-src="{{ $logoDefaultSrc }}"
                        data-white-src="{{ $logoWhiteSrc }}"
                        onerror="this.onerror=null;this.src=this.dataset.defaultSrc;"
                    />
                    <img class="py-2 px-4 w-40 inline-block md:hidden lg:inline-block"
                        src="{{ $useWhiteBrand ? $textWhiteSrc : $textDefaultSrc }}"
                        alt="Classer Text Logo"
                        data-nav-brand-text
                        data-default-src="{{ $textDefaultSrc }}"
                        data-white-src="{{ $textWhiteSrc }}"
                        onerror="this.onerror=null;this.src=this.dataset.defaultSrc;"
                    />
                </a>

                <button class="md:hidden hover:bg-gray-100 p-2 rounded-full" data-global-nav-toggle
                    aria-label="global navigation state Toggle">
                    @icon('menu')
                </button>
            </div>

            <section id="global-nav" class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3">
                @foreach ($navItems as $item)
                    @php
                        $hasChildren = ($item['type'] ?? 'link') === 'dropdown';
                        $itemActive = $hasChildren
                            ? collect($item['children'] ?? [])->contains(fn ($child) => $isActivePath($child['url']))
                            : $isActivePath($item['url']);
                    @endphp

                    @if (! $hasChildren)
                        <a href="{{ $item['url'] }}" class="link {{ $itemActive ? 'underline' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <div
                            x-data="{
                                open: false,
                                toggle() {
                                    if (this.open) {
                                        return this.close(this.$refs.button)
                                    }

                                    this.open = true
                                    this.$nextTick(() => this.$refs.button.focus())
                                },
                                openOnHover() {
                                    if (window.innerWidth >= 768) {
                                        this.open = true
                                    }
                                },
                                close(focusAfter) {
                                    if (!this.open) return
                                    this.open = false
                                    focusAfter && focusAfter.focus()
                                }
                            }"
                            x-on:keydown.escape.prevent.stop="close($refs.button)"
                            x-on:focusin.window="! $refs.panel.contains($event.target) && ! $refs.button.contains($event.target) && close()"
                            x-on:mouseenter="openOnHover()"
                            x-on:mouseleave="if (window.innerWidth >= 768) close()"
                            x-id="['{{ $item['id'] }}-dropdown-button']"
                            class="w-full md:w-auto md:relative"
                        >
                            <button
                                x-ref="button"
                                x-on:click="toggle()"
                                :aria-expanded="open"
                                :aria-controls="$id('{{ $item['id'] }}-dropdown-button')"
                                type="button"
                                class="link inline-flex w-full items-center rounded-md px-2 py-1 transition-colors hover:bg-gray-50 md:w-auto md:hover:bg-transparent"
                            >
                                <span class="{{ $itemActive ? 'underline' : '' }} mr-2 text-left">{{ $item['label'] }}</span>
                                <svg class="ml-auto h-3.5 w-3.5 shrink-0 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                x-ref="panel"
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                x-on:click.outside="close($refs.button)"
                                @focusout="await $nextTick(); !$el.contains($focus.focused()) && close()"
                                :id="$id('{{ $item['id'] }}-dropdown-button')"
                                x-cloak
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg z-20 md:absolute md:left-0 md:min-w-48 md:w-max"
                            >
                                @foreach ($item['children'] as $child)
                                    <a
                                        href="{{ $child['url'] }}"
                                        class="flex w-full items-center rounded-md px-2 py-2 text-left text-gray-800 transition-colors hover:bg-gray-50 lg:py-1.5 {{ $isActivePath($child['url']) ? 'bg-gray-50 font-semibold' : '' }}"
                                    >
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                <form
                    method="POST"
                    action="{{ route('preferences.currency.update') }}"
                    class="mt-2 md:mt-0 md:ml-4"
                    x-data="{ open: false }"
                >
                    @csrf
                    <div class="relative inline-flex items-center">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-800"
                            x-on:click="open = !open"
                            x-on:keydown.escape.prevent.stop="open = false"
                            :aria-expanded="open"
                            aria-label="Select currency"
                        >
                            <span aria-hidden="true">{{ $activeCurrency['symbol'] ?? strtoupper($activeCurrency['code'] ?? 'GBP') }}</span>
                            <span class="hidden md:inline">{{ $activeCurrency['label'] }}</span>
                            <svg class="h-3.5 w-3.5 shrink-0 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            x-on:click.outside="open = false"
                            x-cloak
                            class="absolute right-0 top-full z-20 mt-2 min-w-44 rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg"
                        >
                            @foreach ($currencyOptions as $currencyOption)
                                <button
                                    type="submit"
                                    name="currency"
                                    value="{{ $currencyOption['code'] }}"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-gray-800 transition-colors hover:bg-gray-50 lg:py-1.5 {{ $activeCurrencyCode === $currencyOption['code'] ? 'bg-gray-50 font-semibold' : '' }}"
                                >
                                    <span aria-hidden="true">{{ $currencyOption['symbol'] ?? strtoupper($currencyOption['code']) }}</span>
                                    <span>{{ $currencyOption['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </form>

                <div class="mt-4 md:mt-0 md:ml-6">
                    @include('partials.catalog-item-purchase-form', [
                        'buttonLabel' => 'Buy Now',
                        'formClass' => 'w-full md:w-auto',
                        'catalogItemSkus' => $catalogItemSkus,
                    ])
                </div>
            </section>
        </div>
    </nav>
</section>

@if ($reserveSpace)
    <div
        aria-hidden="true"
        class="site-header-spacer"
        @if (is_string($spacerBackground) && $spacerBackground !== '')
            style="--site-header-spacer-bg: {{ $spacerBackground }};"
        @endif
    ></div>
@endif
