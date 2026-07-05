{{-- Built for the long run. For the memories that matter --}}
@php
    $steps = [
        [
            'title' => 'Connect your hard drive',
            'icon'  => 'tower-server',
            'description' => 'Keep all your adventures in one place',
        ],
        [
            'title' => 'Download Classer',
            'icon'  => 'download',
            'description' => 'Instantly browse years of footage',
        ],
        [
            'title' => 'Classer scans your media',
            'icon'  => 'scan-media',
            'description' => 'No more folders, missing files or duplicate videos'
        ],
        [
            'title' => 'Rediscover forgotten moments',
            'icon'  => 'eye',
            'description' => "Find adventures you haven't seen in years",
        ]
    ];
@endphp

<section class="w-full">
    {{-- Rounded outer shell — image flush left, grey copy panel right --}}
    <div class="overflow-hidden rounded-2xl">
        <div class="grid grid-cols-1 lg:grid-cols-[1.45fr_1fr] items-stretch">
            <div class="min-h-[360px] lg:min-h-[680px]">
                <img
                    alt="Classer app being used on an iPad"
                    class="block h-full w-full"
                    src="{{ Storage::disk('s3')->url('classermedia.com/assets/images/classer-2/device-showcase.jpg') }}"
                />
            </div>

            <article class="bg-[#F6F4F1] px-8 py-8 md:px-12 md:py-10 flex flex-col justify-center">    
                <header>
                    <h2 class="text-2xl md:text-5xl lg:text-4xl text-brand-color mb-3 text-absolute not-italic font-medium leading-[108.54%] text-center">
                        The smart hub and app that unlock your forgotten footage
                    </h2>
                </header>

                <ul class="mx-auto my-7 w-full space-y-6 text-left">
                    @foreach ($steps as $step)
                        <li class="flex items-start gap-3">
                            <span class="mt-[2px] flex flex-shrink-0 items-center justify-center text-[#004b5d] [&_svg]:h-6 [&_svg]:w-6">
                                @icon($step['icon'])
                            </span>

                            <div>
                                <h3 class="text-lg font-bold leading-tight text-[#004b5d]">
                                    {{ $step['title'] }}
                                </h3>

                                @if (!empty($step['description']))
                                    <p class="mt-1 text-base text-[#a0a0a0]">
                                        {{ $step['description'] }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="block md:flex lg:block flex-row items-center gap-4 lg:mt-4">
                    @include('partials.catalog-item-purchase-form', [
                        'buttonLabel' => 'Order now',
                        'formClass' => '',
                        'catalogItemSkus' => [
                            'PRODUCT-J3VQXNTI',
                            'PLAN-NT8P1DOQ',
                        ],
                    ])
                    <span class="w-full block md:w-auto text-center text-sm mt-2">
                        Free Classer software included
                    </span>
                </div>
            </article>
        </div>
    </div>
</section>
