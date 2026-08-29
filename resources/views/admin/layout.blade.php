@php
    $topLevelNavItems = [
        [
            'section' => 'users',
            'label' => 'Users',
            'icon' => 'users',
            'url' => url('/admin/users'),
        ],
        [
            'section' => 'cloud-shares',
            'label' => 'Cloud Share',
            'icon' => 'share',
            'url' => url('/admin/cloud-shares'),
        ],
        [
            'section' => 'orders',
            'label' => 'Orders',
            'icon' => 'book',
            'url' => url('/admin/orders'),
        ],
    ];

    $navGroups = [
        [
            'label' => 'E-Commerce',
            'items' => [
                [
                    'section' => 'products',
                    'label' => 'Products',
                    'icon' => 'barcode-2',
                    'url' => url('/admin/products'),
                ],
                [
                    'section' => 'plans',
                    'label' => 'Plans',
                    'icon' => 'barcode-2',
                    'url' => url('/admin/plans'),
                ],
                [
                    'section' => 'discount-codes',
                    'label' => 'Discount Codes',
                    'icon' => 'discount',
                    'url' => url('/admin/discount-codes'),
                ],
                [
                    'section' => 'shipping',
                    'label' => 'Shipping',
                    'icon' => 'pin',
                    'url' => url('/admin/shipping'),
                ],
                [
                    'section' => 'currencies',
                    'label' => 'Currencies',
                    'icon' => 'currencies',
                    'url' => url('/admin/currencies'),
                ],
            ],
        ],
        [
            'label' => 'Content',
            'items' => [
                [
                    'section' => 'posts',
                    'label' => 'Posts',
                    'icon' => 'article',
                    'url' => url('/admin/posts'),
                ],
                [
                    'section' => 'faqs',
                    'label' => 'FAQs',
                    'icon' => 'menu',
                    'url' => url('/admin/faqs'),
                ],
                [
                    'section' => 'tutorials-items',
                    'label' => 'Tutorials',
                    'icon' => 'app',
                    'url' => url('/admin/tutorials-items'),
                ],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                [
                    'section' => 'stats',
                    'label' => 'Stats',
                    'icon' => 'stats',
                    'url' => url('/admin/stats'),
                ],
                [
                    'section' => 'scheduler',
                    'label' => 'Scheduler',
                    'icon' => 'tower-server',
                    'url' => url('/admin/scheduler'),
                ],
                [
                    'section' => 'email-broadcasts',
                    'label' => 'Email Broadcasts',
                    'icon' => 'email',
                    'url' => url('/admin/email-broadcasts'),
                ],
                [
                    'section' => 'logs',
                    'label' => 'Logs',
                    'icon' => 'logs',
                    'url' => url('/admin/logs'),
                ],
            ],
        ],
    ];

    $baseNavClass = 'admin-btn admin-btn-neutral';
    $activeNavClass = 'admin-btn-success-soft';
    $inactiveNavClass = 'border-transparent text-admin-muted hover:border-zinc-400 hover:text-admin-ink';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Admin</title>

    @include('partials.meta')
</head>

<body>
    <main class="admin-root relative flex h-screen flex-col overflow-hidden">
        @include('partials.navigation', ['spacerBackground' => '#f7f3ee'])

        <section class="overflow-hidden h-full w-full relative z-10 border border-zinc-400 bg-white flex max-w-screen-2xl mx-auto my-4 md:my-12 rounded-xl">
            <aside class="border-r border-zinc-400 p-4 flex flex-col justify-start gap-4 h-full overflow-y-auto">
                <div class="admin-btn admin-btn-success-soft" aria-live="polite">
                    <span class="w-4 h-4 rounded-full bg-green-500 shrink-0" aria-hidden="true"></span>
                    <span id="admin-user-email">{{ auth()->user()->email }}</span>
                </div>

                <nav class="flex flex-col gap-3" aria-label="Admin navigation">
                    @foreach ($topLevelNavItems as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="{{ $baseNavClass }} {{ $activeSection === $item['section'] ? $activeNavClass : $inactiveNavClass }}"
                        >
                            <span class="w-4 h-4 ">
                                @icon($item['icon'])
                            </span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    @foreach ($navGroups as $group)
                        <div class="flex flex-col gap-[0.35rem]" role="group" aria-label="{{ $group['label'] }}">
                            <p class="mb-[0.1rem] px-1 text-[0.68rem] font-bold tracking-[0.08em] uppercase text-[#7b8794]">{{ $group['label'] }}</p>

                            @foreach ($group['items'] as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    class="admin-btn admin-btn-neutral {{ $baseNavClass }} {{ $activeSection === $item['section'] ? $activeNavClass : $inactiveNavClass }}"
                                >
                                    <span class="w-4 h-4">@icon($item['icon'])</span>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>

                <a href="{{ route('admin.logout') }}" class="admin-btn admin-btn-neutral w-full">Log out</a>
            </aside>

            <section class="p-5 overflow-y-auto flex flex-col flex-1 h-full space-y-4" data-admin-section="{{ $activeSection }}">
                <x-admin.breadcrumbs />

                <x-admin.flash-messages />

                @yield('content')
            </section>
        </section>
    </main>
</body>

</html>
