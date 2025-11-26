@php
    $title = "Download Classer";
    $subtitle = "Select the appropriate version for your computer to start downloading Classer.";

    $downloads = [
        [
            'label' => 'Windows',
            'sub'   => 'Windows 10 or later',
            'href'  => url('/download?platform=win'),
            'icon'  => 'windows',
            'divider' => true,
        ],
        [
            'label' => 'MacOS (Apple Silicon)',
            'sub'   => 'For M1, M2, M3 chips • macOS 10.14+',
            'href'  => url('/download?platform=mac&architecture=arm64'),
            'icon'  => 'apple',
            'divider' => false,
        ],
        [
            'label' => 'MacOS (Intel)',
            'sub'   => 'For Intel-based Macs • macOS 10.14+',
            'href'  => url('/download?platform=mac&architecture=x64'),
            'icon'  => 'apple',
            'divider' => false,
        ],
    ];

    // Use your uploaded image path as instructed
    $previewImg = asset('assets/images/welcome/hero/image-3.jpg');
@endphp


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Privacy Policy</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body class="flex flex-col h-lvh">
    @include('partials.shared.navigation')

    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            {{-- Custom 38% / 62% grid split on large screens --}}
            <div class="grid gap-10 lg:grid-cols-[38%_62%] lg:items-center">

                {{-- LEFT COLUMN --}}
                <div class="max-w-md space-y-6">

                    {{-- TITLE w/ forced line break --}}
                    <h2 class="text-4xl text-brand-color md:text-5xl font-bold tracking-tight leading-tight">
                        Download <span class="">Classer</span>
                    </h2>

                    <p class="text-base lg:max-w-md text-slate-600 leading-relaxed">
                        Select the appropriate version for your computer to start downloading Classer.
                    </p>

                    {{-- DOWNLOAD OPTIONS --}}
                    <div class="space-y-6 w-80">
                        @foreach ($downloads as $d)
                            <a
                                target="_blank"
                                href="{{ $d['href'] }}"
                                class="flex items-center gap-5 group cursor-pointer"
                            >
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
                <div class="relative flex justify-center lg:justify-end my-16 md:my-3 lg:my-40 ">
                    <img 
                        src="{{ $previewImg }}" 
                        alt="Classer app preview"
                        class="w-full max-w-3xl shadow-2xl lg:opacity-0"
                    >
                    <img 
                        src="{{ $previewImg }}" 
                        alt="Classer app preview large"
                        class="w-full max-w-3xl shadow-2xl absolute z-10 left-[23%] scale-[1.5] hidden lg:block"
                    >
                </div>

            </div>
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>

