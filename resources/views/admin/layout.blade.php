@php
    $topLevelNavItems = [
        [
            'section' => 'users',
            'label' => 'Users',
            'icon' => 'users',
            'url' => url('/admin/users'),
        ],
    ];

    $navGroups = [
        [
            'label' => 'E-Commerce',
            'items' => [
                [
                    'section' => 'orders',
                    'label' => 'Orders',
                    'icon' => 'book',
                    'url' => url('/admin/orders'),
                ],
                [
                    'section' => 'products',
                    'label' => 'Products',
                    'icon' => 'barcode',
                    'url' => url('/admin/products'),
                ],
                [
                    'section' => 'plans',
                    'label' => 'Plans',
                    'icon' => 'repeat',
                    'url' => url('/admin/plans'),
                ],
                [
                    'section' => 'discount-codes',
                    'label' => 'Discount Codes',
                    'icon' => 'tag',
                    'url' => url('/admin/discount-codes'),
                ],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                [
                    'section' => 'stats',
                    'label' => 'Stats',
                    'icon' => 'location',
                    'url' => url('/admin/stats'),
                ],
                [
                    'section' => 'trends',
                    'label' => 'Trends',
                    'icon' => 'trending-up',
                    'url' => url('/admin/trends'),
                ],
                [
                    'section' => 'bulk-mails',
                    'label' => 'Bulk Emails',
                    'icon' => 'location',
                    'url' => url('/admin/bulk-mails'),
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

    $baseNavClass = 'border rounded-[0.7rem] py-[0.7rem] px-[0.85rem] font-semibold no-underline transition-all duration-[140ms] flex items-center gap-2';
    $activeNavClass = 'border-[#b8dfdc] bg-admin-primary-soft text-admin-primary';
    $inactiveNavClass = 'border-transparent text-admin-muted hover:border-admin-stroke hover:text-admin-ink';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Admin</title>

    @include('partials.meta')
    @vite('resources/views/admin/app/index.css')
    @vite('resources/views/admin/app/index.js')
</head>

<body>
    @include('partials.navigation')

    {{-- .admin-root kept only for its ::before gradient pseudo-element in index.css --}}
    <main class="admin-root relative flex items-center justify-center p-8 overflow-hidden" style="height: calc(100vh - 64px);">
        @include('partials.triangles')

        <section class="relative z-10 w-full h-[calc(100vh-8rem)] max-h-[calc(100vh-8rem)] border border-admin-stroke bg-white rounded-[1.25rem] shadow-[0_30px_80px_rgba(20,42,53,0.12)] flex overflow-hidden">
            <aside class="border-r border-admin-stroke pt-7 px-4 pb-4 flex flex-col justify-start gap-4 bg-gradient-to-b from-[#fcfefe] to-[#f6fafc] h-full overflow-y-auto">
                <div class="flex flex-col items-stretch gap-[0.85rem] px-[0.3rem]">
                    <div class="inline-flex items-center gap-[0.4rem] w-fit max-w-full border border-[#d8e5dd] bg-[#f4fbf7] rounded-full py-[0.22rem] px-[0.62rem] text-[#1f4d33] text-[0.76rem] font-semibold overflow-hidden text-ellipsis whitespace-nowrap" aria-live="polite">
                        <span class="w-[0.48rem] h-[0.48rem] rounded-full bg-green-500 shadow-[0_0_0_3px_rgba(34,197,94,0.15)] shrink-0" aria-hidden="true"></span>
                        <span id="admin-user-email">{{ auth()->user()->email }}</span>
                    </div>

                    <nav class="flex flex-col gap-3 mt-[0.35rem]" aria-label="Admin navigation">
                        @foreach ($topLevelNavItems as $item)
                            <a
                                href="{{ $item['url'] }}"
                                class="{{ $baseNavClass }} {{ $activeSection === $item['section'] ? $activeNavClass : $inactiveNavClass }}"
                            >
                                <span class="w-4 h-4 inline-flex items-center justify-center">
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
                                        class="{{ $baseNavClass }} {{ $activeSection === $item['section'] ? $activeNavClass : $inactiveNavClass }}"
                                    >
                                        <span class="w-4 h-4 inline-flex items-center justify-center">
                                            @icon($item['icon'])
                                        </span>
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>
                </div>

                <!-- <button id="admin-logout" class="appearance-none border border-[#d4dbe1] bg-white text-[#42515c] rounded-[0.7rem] py-[0.65rem] px-[0.85rem] font-semibold text-left mt-auto hover:border-[#b8c3ce] cursor-pointer">Log out</button> -->
                <a href="{{ route('admin.logout') }}" class="appearance-none border border-[#d4dbe1] bg-white text-[#42515c] rounded-[0.7rem] py-[0.65rem] px-[0.85rem] font-semibold text-left mt-auto hover:border-[#b8c3ce] cursor-pointer">Log out</a>
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
