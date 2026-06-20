<section id="nav" class="w-full sticky top-0 bg-transparent z-50 transition-all duration-500">
    <nav class="flex items-center max-w-7xl m-auto p-3 md:justify-between flex-col md:flex-row">
        <div class="flex justify-between  items-center gap-4 w-full md:w-auto">
            <a href="{!! url('/') !!}" class="flex items-center">
                <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/brand/classer-logo.svg') }}"
                    alt="Classer Symbol Logo" />
                <img class="py-2 px-4 w-40 inline-block md:hidden lg:inline-block"
                    src="{{ asset('/assets/images/brand/classer-text.svg') }}" alt="Classer Text Logo" />
            </a>

            <button class="md:hidden hover:bg-gray-100 p-2 rounded-full" data-global-nav-toggle
                aria-label="global navigation state Toggle">
                @icon('menu')
            </button>
        </div>

        <section id="global-nav" class="flex md:flex">
            @php
                $navItems = [
                    ['label' => 'Home', 'url' => url(''), 'class' => 'link'],
                    ['label' => 'Blog', 'url' => url('/blog'), 'class' => 'link'],
                    ['label' => 'Stories', 'url' => url('/stories'), 'class' => 'link'],
                    ['label' => 'Find your best action cam', 'url' => url('/action-camera-matcher'), 'class' => 'link'],
                    [
                        'label' => 'Download',
                        'url' => url('/download'),
                        'class' => 'link' . (request()->is('download') ? ' underline' : ''),
                    ],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php
                    // current request path ('' for home)
                    $currentPath = trim(parse_url(request()->getRequestUri(), PHP_URL_PATH), '/');

                    // item path from the configured URL ('' for home)
                    $itemPath = trim(parse_url($item['url'], PHP_URL_PATH), '/');

                    // active when both are root, or when current path starts with the nav item path
                    $active =
                        ($itemPath === '' && $currentPath === '') ||
                        ($itemPath !== '' && \Illuminate\Support\Str::startsWith($currentPath, $itemPath));
                @endphp

                <a href="{{ $item['url'] }}" class="{{ $item['class'] }} {{ $active ? 'underline' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="mt-4 md:mt-0 md:ml-6">
                @include('partials.shared.catalog-item-purchase-form', [
                    'buttonLabel' => 'Buy Classer',
                    'formClass' => '',
                    'catalogItemSkus' => [
                        'PRODUCT-AGBLENAG',
                        'PLAN-VBPM62WB',
                    ],
                ])
            </div>
        </section>
    </nav>
</section>
