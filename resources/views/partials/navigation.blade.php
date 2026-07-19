@php
    $currentPath = trim(parse_url(request()->getRequestUri(), PHP_URL_PATH), '/');
    $isHomePage = $currentPath === '';
    $navState = $state ?? ($isHomePage ? 'transparent' : 'default');
    $isTransparent = $navState === 'transparent';
    $navStartOffset = max(0, (int) ($startOffset ?? 0));
    $reserveSpace = (bool) ($reserveSpace ?? ! $isTransparent);
    $spacerBackground = $spacerBackground ?? null;

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
                ['label' => 'Download', 'url' => url('/download')],
                ['label' => 'Features', 'url' => url('/app')],
                ['label' => 'Guides', 'url' => url('/guides')],
            ],
        ],
        [
            'type' => 'dropdown',
            'id' => 'discover',
            'label' => 'Discover',
            'children' => [
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
            height: var(--site-header-height);
            background: var(--site-header-spacer-bg, transparent);
        }

        /*
         * Pull a full-bleed section (e.g. a hero) up behind the transparent nav.
         * Use on the first content element on transparent-nav pages instead of a spacer.
         */
        .nav-overlap {
            margin-top: calc(-1 * var(--site-header-height));
        }
    </style>
@endonce

<section
    id="nav"
    class="site-header {{ $isTransparent ? 'site-header--transparent' : 'site-header--default' }} w-full"
    style="--nav-start-offset: {{ $isTransparent ? $navStartOffset : 0 }}px;"
>
    <nav class="h-full w-full px-4 md:px-6">
        <div class="max-w-7xl m-auto flex items-center md:justify-between flex-col md:flex-row">
            <div class="flex justify-between items-center gap-4 w-full md:w-auto w-full py-3 md:py-5">
                <a href="{!! url('/') !!}" class="flex items-center">
                    <img class="py-2 w-12 md:w-8" src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-logo.svg') }}"
                        alt="Classer Symbol Logo" />
                    <img class="py-2 px-4 w-40 inline-block md:hidden lg:inline-block"
                        src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/brand/classer-text.svg') }}" alt="Classer Text Logo" />
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
                                close(focusAfter) {
                                    if (!this.open) return
                                    this.open = false
                                    focusAfter && focusAfter.focus()
                                }
                            }"
                            x-on:keydown.escape.prevent.stop="close($refs.button)"
                            x-on:focusin.window="! $refs.panel.contains($event.target) && ! $refs.button.contains($event.target) && close()"
                            x-id="['{{ $item['id'] }}-dropdown-button']"
                            class="relative"
                        >
                            <button
                                x-ref="button"
                                x-on:click="toggle()"
                                :aria-expanded="open"
                                :aria-controls="$id('{{ $item['id'] }}-dropdown-button')"
                                type="button"
                                class="link flex inline-flex items-center gap-2"
                            >
                                <span class="{{ $itemActive ? 'underline' : '' }}">{{ $item['label'] }}</span>
                            </button>

                            <div
                                x-ref="panel"
                                x-show="open"
                                x-transition.origin.top.left
                                x-on:click.outside="close($refs.button)"
                                :id="$id('{{ $item['id'] }}-dropdown-button')"
                                x-cloak
                                class="absolute left-0 mt-2 min-w-48 rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm z-20"
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

                <div class="mt-4 md:mt-0 md:ml-6">
                    <a href="{{ url('/products/classer-home') }}" class="btn btn-lg uppercase">
                        Buy Classer
                    </a>
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
