{{-- Built for the long run. For the memories that matter --}}
@php
    $steps = [
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m4.5-6H16.5m-1.5 3H16.5m-1.5 3H16.5M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>',
            'title' => 'Connect your hard drive to your device',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>',
            'title' => 'Download our software online Classer',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>',
            'title' => 'When you select your media files, Classer will scan and analyse your media files',
        ],
        [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
            'title' => 'Start browsing, organising, sharing and recording the location of your footage with the Classer app',
        ],
    ];
@endphp

<div class="mx-auto w-full max-w-[1420px] px-4 md:px-7">

    {{-- Rounded outer shell — image flush left, grey copy panel right --}}
    <div class="overflow-hidden rounded-[32px]">
        <div class="grid grid-cols-1 lg:grid-cols-[1.45fr_1fr] items-stretch">

            {{-- Image: fills its column edge-to-edge, full height --}}
            <div class="min-h-[360px] lg:min-h-[680px]">
                <img
                    src="https://placeholders.io/700/500"
                    alt="Classer app being used on an iPad"
                    class="block h-full w-full object-cover"
                />
            </div>

            {{-- Grey copy panel --}}
            <div class="bg-[#efece8] px-8 py-12 md:px-12 md:py-16 lg:px-14 flex flex-col justify-center">

                <h2 class="text-brand-color text-center text-[34px] md:text-[40px] lg:text-[46px] leading-[1.15] font-semibold tracking-[-0.02em] mb-12 md:mb-14">
                    Built for the long run.<br>
                    For the memories<br>
                    that matter
                </h2>

                <ul class="space-y-7 md:space-y-8 max-w-[340px] mx-auto w-full">
                    @foreach ($steps as $step)
                        <li class="flex items-start gap-4">
                            <span class="flex-shrink-0 text-brand-color/80 mt-0.5 [&_svg]:h-6 [&_svg]:w-6">
                                {!! $step['icon'] !!}
                            </span>
                            <p class="text-[15px] md:text-[16px] leading-[1.45] text-[#9a9a9a]">
                                {{ $step['title'] }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>
</div>
