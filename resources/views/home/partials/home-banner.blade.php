{{-- Your hard drives hold your best adventures --}}
@php
    $features = [
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
            'title' => 'Automatic',
            'desc'  => 'Your footage is always ready, connected to your hard drive',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5"/></svg>',
            'title' => 'Private by design',
            'desc'  => 'Everything stays on your network and your devices',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6"><rect x="3" y="5" width="12" height="14" rx="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m6 15 2.5-3 2 2.5 1.5-1.5 3 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 7h2v4h-2zM19 14h2v4h-2z"/></svg>',
            'title' => 'Made for rediscovery',
            'desc'  => 'Collections bring your best moments back to you',
        ],
    ];
@endphp

<section class="w-full">
    <section class="relative overflow-hidden rounded-2xl aspect-[4/2] md:aspect-[14/13] lg:aspect-[14/7]">
        {{-- Background image --}}
        <img
            class="absolute inset-0 h-full w-full md:object-cover md:object-[left_70%_top_50%] lg:object-center z-0"
            src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/classer-2/deviceshowcase4k.jpg') }}"
        />
        {{-- Dark readability overlays --}}
        <!-- <div class="absolute inset-0 bg-black/20"></div> -->
        <div class="md:absolute inset-y-0 right-0 w-full bg-gradient-to-b from-black/100 via-black/20 to-black/30 md:w-[100%] md:bg-gradient-to-l md:from-black/75 md:via-black/25 md:to-transparent"></div>

        {{-- Content --}}
        <div class="relative h-full z-10 flex items-start md:items-center justify-end p-4 lg:py-16">
            <div class="hidden md:block w-full md:max-w-[380px] xl:max-w-[420px] text-white">
                <h2 class="text-2xl md:text-[40px] mb-6 text-center md:text-left leading-[1.1]">
                    Your hard drives hold your best <span class="font-acent text-[46px]"> adventures. </span> <br> We give them a screen
                </h2>

                <div class="space-y-7">
                    @foreach ($features as $f)
                        <div class="flex gap-4">
                            <!-- <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center text-white">
                                {!! $f['icon'] !!}
                            </div> -->

                            <div>
                                <h3 class="text-base font-semibold leading-tight tracking-widest uppercase">
                                    {{ $f['title'] }}
                                </h3>
                                <p class="mt-1 max-w-[265px] text-base leading-snug md:text-white/90">
                                    {{ $f['desc'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex gap-4">
                        <a href="{{ url('/app') }}" class="btn btn-lg btn-white uppercase">
                            Explore The App
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile Content --}}
    <section class="space-y-7 mt-6 md:hidden m-auto">
        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-6 text-absolute not-italic font-medium leading-[108.54%] text-center">
            Your hard drives hold your best <span class="font-acent text-4xl md:text-5xl lg:text-6xl"> adventures. </span> <br> We give them a screen
        </h2>

        @foreach ($features as $f)
            <article class="flex gap-4 mt-3">
                <!-- <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center">
                    {!! $f['icon'] !!}
                </div> -->

                <div>
                    <h3 class="text-lg font-semibold leading-tight uppercase tracking-widest">
                        {{ $f['title'] }}
                    </h3>

                    <p class="mt-1 text-base leading-snug">
                        {{ $f['desc'] }}
                    </p>
                </div>
            </article>
        @endforeach

        <div class="flex w-full justify-center">
            @include('partials.catalog-item-purchase-form', [
                'buttonLabel' => 'Order now',
                'formClass' => '',
                'catalogItemSkus' => [
                    'PRODUCT-J3VQXNTI',
                    'PLAN-NT8P1DOQ',
                ],
            ])
        </div>
    </section>
</section>