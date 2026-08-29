@php
    $title = 'Download Classer';
    $subtitle = 'Select the appropriate version for your computer to start downloading Classer.';

    $downloads = [
        [
            'label' => 'Windows',
            'sub' => 'Windows 10 or later',
            'href' => route('download', ['platform' => 'win']),
            'icon' => 'windows',
            'divider' => true,
        ],
        [
            'label' => 'MacOS (Apple Silicon)',
            'sub' => 'For M1, M2, M3 chips • macOS 10.14+',
            'href' => route('download', ['platform' => 'mac', 'architecture' => 'arm64']),
            'icon' => 'apple',
            'divider' => false,
        ],
        [
            'label' => 'MacOS (Intel)',
            'sub' => 'For Intel-based Macs • macOS 10.14+',
            'href' => route('download', ['platform' => 'mac', 'architecture' => 'x64']),
            'icon' => 'apple',
            'divider' => false,
        ],
    ];

    $previewImages = [
        [
            'src' => $cloudAssetUrl('assets/images/welcome/hero/image-3.jpg'),
            'alt' => 'A screen shot of the Classer media view in dark mode.',
        ],

        [
            'src' => $cloudAssetUrl('assets/images/welcome/hero/image-1.jpg'),
            'alt' => 'A screen shot of the Classer app showing an overview of recordings.',
        ],
        [
            'src' => $cloudAssetUrl('assets/images/welcome/hero/image-4.jpg'),
            'alt' => 'A screen shot of the Classer media detail view.',
        ],
        [
            'src' => $cloudAssetUrl('assets/images/welcome/hero/image-2.jpg'),
            'alt' => 'A screen shot of the Classer app in dark mode showing recordings.',
        ],
        [
            'src' => $cloudAssetUrl('assets/images/welcome/hero/image-5.jpg'),
            'alt' => 'A screen shot of the Classer media timeline and controls.',
        ],
    ];
@endphp


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body>
    @include('partials.navigation')
    @include('partials.modals')

    <div class="my-8 lg:my-0"></div>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                <div class="grid gap-10 lg:grid-cols-[38%_62%] lg:items-center">

                    {{-- LEFT COLUMN --}}
                    <div class="max-w-md space-y-6">

                        {{-- TITLE w/ forced line break --}}
                        <h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color mb-3 font-medium leading-[108.54%]">
                            Download <span class="">Classer</span>
                        </h2>

                        <p class="text-base lg:max-w-md text-slate-600 leading-relaxed">
                            Select the appropriate version for your computer to start downloading Classer.
                        </p>

                        {{-- DOWNLOAD OPTIONS --}}
                        <div class="space-y-6 w-80">
                            @foreach ($downloads as $d)
                                <a target="_blank" href="{{ $d['href'] }}"
                                    class="flex items-center gap-5 group cursor-pointer">
                                    <span class="text-blue-500 fill-current hover:text-blue-700">
                                        @if ($d['icon'] === 'apple')
                                            @icon(apple)
                                        @elseif ($d['icon'] === 'windows')
                                            @icon(windows)
                                        @endif
                                    </span>

                                    <div>
                                        <p class="text-xl text-sky-500 font-bold group-hover:text-sky-700">
                                            {{ $d['label'] }}
                                        </p>
                                        <p class="text-sm text-slate-600">{{ $d['sub'] }}</p>
                                    </div>
                                </a>

                                @if (isset($d['divider']) && $d['divider'])
                                    <hr class="my-6 border-gray-300">
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- RIGHT COLUMN IMAGE (NOW 62% width area) --}}
                    <div
                        class="relative my-16 flex justify-center md:my-3 lg:my-40 lg:justify-end"
                        x-data="{
                            images: @js($previewImages),
                            activeIndex: 0,
                            intervalId: null,
                            init() {
                                if (this.images.length <= 1) {
                                    return;
                                }

                                this.intervalId = window.setInterval(() => {
                                    this.activeIndex = (this.activeIndex + 1) % this.images.length;
                                }, 4200);
                            },
                            isActive(index) {
                                return this.activeIndex === index;
                            }
                        }"
                    >
                        <img
                            src="{{ $previewImages[0]['src'] }}"
                            alt="{{ $previewImages[0]['alt'] }}"
                            class="w-full max-w-3xl opacity-0"
                        >

                        <div class="absolute inset-0 lg:left-[23%] lg:scale-[1.8]  top-1/2  -translate-y-1/2 -mt-12 lg:-translate-y-1/2">
                            <template x-for="(image, index) in images" :key="image.src">
                                <img
                                    :src="image.src"
                                    :alt="image.alt"
                                    class="absolute inset-0 w-full max-w-3xl rounded-md shadow-2xl transition-opacity duration-[1400ms] ease-in-out"
                                    :class="isActive(index) ? 'opacity-100' : 'opacity-0'"
                                >
                            </template>
                        </div>
                    </div>

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
</body>

</html>
