{{-- How It Works Section --}}
<div class="mx-auto w-full max-w-7xl px-8">

        {{-- Section Heading --}}
        <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight mb-6 text-brand text-center text-brand-color">
            Designed to quietly look after your footage, so you can focus on your next adventure
        </h2>

        @php
            $steps = [
                [
                    'label' => 'Connect your devices',
                    'text' => 'Classer Home imports your footage directly to your hard drive.',
                    'text2' => 'Your hard drive stores the footage, while Classer Home makes it meaningful.',
                    'image' => asset('/assets/images/classer-home/step-01.png'),
                    'alt' => 'Classer Home connected to an external hard drive',
                    'imagePosition' => 'left',
                ],
                [
                    'label' => 'Create collections',
                    'text' => 'Classer Home turns raw files into meaningful collections, preserving GPS, telemetry and camera data.',
                    'text2' => 'It works quietly in the background while you get on with your day.',
                    'image' => asset('/assets/images/classer-home/step-02.png'),
                    'alt' => 'Classer Home organising footage into collections',
                    'imagePosition' => 'right',
                ],
                [
                    'label' => 'Browse all your adventures',
                    'text' => 'Browse your adventures as collections, organised by trips, days, and activities in the app. Export or share them anytime.',
                    'text2' => 'Compatible with Mac and Windows. For desktop and iPad/ tablets.',
                    'image' => asset('/assets/images/classer-home/step-03.png'),
                    'alt' => 'Browse your adventures on Mac and Windows',
                    'imagePosition' => 'left',
                ],
            ];
        @endphp

        @foreach ($steps as $index => $step)
            <div class="mt-12 flex flex-col {{ $step['imagePosition'] === 'right' ? 'md:flex-row-reverse' : 'md:flex-row' }} items-center gap-6 md:gap-0 mx-auto {{ !$loop->last ? 'mb-24 md:mb-32' : '' }}">

                {{-- Image — bleeds outside container --}}
                <div class="w-full md:w-[65%] {{ $step['imagePosition'] === 'left' ? 'md:-ml-16 lg:-ml-24' : 'md:-mr-16 lg:-mr-24' }} flex-shrink-0">
                    <div class="overflow-hidden rounded-2xl">
                        <img
                            src="{{ $step['image'] }}"
                            alt="{{ $step['alt'] }}"
                            class="w-full h-auto object-cover"
                        />
                    </div>
                </div>

                {{-- Text --}}
                <div class="w-full md:w-[35%] {{ $step['imagePosition'] === 'left' ? 'md:pl-10 lg:pl-16' : 'md:pr-10 lg:pr-16' }} flex flex-col justify-center">
                    <p class="text-xs font-medium tracking-[0.3em] uppercase mb-3" style="font-family: 'Fira Code', monospace; color: #1a3c4a;">
                        {{ $step['label'] }}
                    </p>
                    <p class="text-gray-500 text-base md:text-lg leading-relaxed">
                        {{ $step['text'] }}
                    </p>
                    @if (!empty($step['text2']))
                        <p class="text-gray-500 text-base md:text-lg leading-relaxed mt-4">
                            {{ $step['text2'] }}
                        </p>
                    @endif
                </div>

            </div>
        @endforeach

    </div>
