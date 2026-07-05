 @php
    $includedItems = [
        [
            'label' => 'Classer Home<br>device',
            'icon' => '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="10" rx="1.5" /><path d="M7 17v2h10v-2" /><path d="M7 10h6" /><path d="M16 10h1" /></svg>',
        ],
        [
            'label' => 'Ethernet<br>cable',
            'icon' => '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 4v16" /><path d="M16 4v16" /><path d="M8 8h8" /><path d="M8 16h8" /><circle cx="8" cy="4" r="1.5" /><circle cx="16" cy="4" r="1.5" /><circle cx="8" cy="20" r="1.5" /><circle cx="16" cy="20" r="1.5" /></svg>',
        ],
        [
            'label' => 'Desktop<br>app',
            'icon' => '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="11" rx="1.5" /><path d="M12 16v3" /><path d="M8 19h8" /></svg>',
        ],
        [
            'label' => 'Future<br>updates',
            'icon' => '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 4v5" /><path d="M8 9h8" /><path d="M10 9v5a3 3 0 1 0 6 0v-1" /><path d="M16 13h3" /><path d="M19 10v6" /></svg>',
        ],
        [
            'label' => 'Priority<br>support',
            'icon' => '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 13a8 8 0 0 1 16 0" /><path d="M4 13v3a2 2 0 0 0 2 2h1v-5H4z" /><path d="M20 13v3a2 2 0 0 1-2 2h-1v-5h3z" /><path d="M9 19h6" /></svg>',
        ],
    ];
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer Home - We record everything. We remember almost nothing.</title>
    @include('partials.meta')
    @vite('resources/css/app.css')
    @vite('resources/css/markdown/main.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
</head>

<body class="antialiased">
    @include('partials.navigation')

    <style>
        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

<div class="bg-[#fafafa]">
    <div aria-hidden="true" class="header-blocker" style="--header-blocker-bg: #f7f3ee;"></div>

    <section class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.08fr_0.92fr] lg:gap-14">
            <x-product-gallery :gallery="$gallery" />

            <div>
                <div class="max-w-xl">
                    <h1 class="text-4xl font-semibold tracking-tight text-[#073f4d] sm:text-5xl">
                        {{ $product['title'] }}
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-[#51727a]">
                        Organise, browse and relive years of videos without the folder mess.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @if ($product['hasPromotion'])
                            <span class="text-sm text-[#51727a] line-through">
                                £{{ $product['originalPrice'] }}
                            </span>
                        @endif

                        <div class="flex items-end text-[#073f4d]">
                            <span class="text-4xl font-bold leading-none sm:text-5xl">
                                £{{ explode('.', $product['priceAmountFormatted'])[0] }}
                            </span>
                            <span class="mb-1 ml-1 text-lg font-bold">
                                .{{ explode('.', $product['priceAmountFormatted'])[1] }}
                            </span>
                        </div>

                        @if ($product['hasPromotion'])
                            <span class="rounded-lg bg-[#eef1e9] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#6f7b69]">
                                Save {{ $product['promotionPercentage'] }}%
                            </span>
                        @endif
                    </div>

                    {{-- Included --}}
                    <div class="mt-8">
                        <h2 class="text-sm font-bold text-[#0d4150]">
                            What’s included
                        </h2>

                        <div class="mt-5 grid grid-cols-5 gap-3 text-center max-sm:grid-cols-3">
                            @foreach ($includedItems as $item)
                                <div class="flex flex-col items-center gap-2">
                                    <div class="text-[#0d5666]">
                                        {!! $item['icon'] !!}
                                    </div>

                                    <span class="text-[11px] leading-tight text-[#51727a]">
                                        {!! $item['label'] !!}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Plus Box --}}
                    <div class="mt-7 rounded-2xl bg-[#edf8ef] p-5">
                        <h3 class="text-base font-bold text-[#07551f]">
                            Plus includes, FREE
                            <a href="{{ url('/classer-share') }}" class="underline underline-offset-2">
                                Classer Share
                            </a>
                            for 6 months
                        </h3>

                        <p class="mt-2 text-sm leading-5 text-[#07551f]">
                            Send selected clips to friends and family with a private link that expires after 24 hours.
                        </p>
                    </div>

                    {{-- Specs --}}
                    <div class="mt-7 border-t border-[#e4e7e3] pt-5">
                        <h2 class="text-sm font-bold text-[#0d4150]">
                            Specs
                        </h2>

                        <dl class="mt-4 space-y-3 text-sm leading-5 text-[#8a8f8d]">
                            @foreach ($specs as $label => $value)
                                <div>
                                    <dt class="inline text-[#8a8f8d]">
                                        {{ $label }}:
                                    </dt>
                                    <dd class="inline">
                                        {{ $value }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    {{-- Works With --}}
                    <div class="mt-7 border-t border-[#e4e7e3] pt-5">
                        <h2 class="text-sm font-bold text-[#0d4150]">
                            Works with
                        </h2>

                        <ul class="mt-4 space-y-3 text-sm leading-5 text-[#8a8f8d]">
                            @foreach ($worksWith as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="bg-white pb-36 md:pb-28">
    <div aria-hidden="true" class="header-blocker" style="--header-blocker-bg: #ffffff;"></div>

    <x-sticky-bottom-purchase-banner :sticky-products="$stickyProducts" />
</div>

</body>