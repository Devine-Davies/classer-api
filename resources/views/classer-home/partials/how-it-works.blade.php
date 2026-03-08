{{-- How It Works Section --}}
<div class="mx-auto w-full px-8" style="max-width: 1400px;">

        {{-- Section Heading --}}
        <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight mb-6 text-brand text-center text-brand-color">
            Designed to quietly look after your footage, so you can focus on your next adventure
        </h2>

        @php
            $steps = [
                [
                    'label' => 'Step 01',
                    'text' => 'Connect your external hard drive and insert your camera or SD card.',
                    'image' => asset('/assets/images/classer-home/step-01.png'),
                    'alt' => 'Classer Home connected to an external hard drive',
                    'imagePosition' => 'left',
                ],
                [
                    'label' => 'Step 02',
                    'text' => 'Classer copies your footage to your hard drive, keeps your folder structure clean, and preserves GPS and camera data. Your original files remain untouched.',
                    'image' => asset('/assets/images/classer-home/step-02.png'),
                    'alt' => 'Classer Home organising footage into collections',
                    'imagePosition' => 'right',
                ],
                [
                    'label' => 'Step 03',
                    'text' => 'Browse your adventures as collections, organised by trips, days, and activities. Export or share them anytime.',
                    'text2' => 'Compatible with Mac and Windows. For desktop and iPad/ tablets.',
                    'image' => asset('/assets/images/classer-home/step-03.png'),
                    'alt' => 'Browse your adventures on Mac and Windows',
                    'imagePosition' => 'left',
                ],
            ];
        @endphp

        @foreach ($steps as $index => $step)
            <div class="mt-12 flex flex-col {{ $step['imagePosition'] === 'right' ? 'md:flex-row-reverse' : 'md:flex-row' }} items-center gap-8 md:gap-16 mx-auto {{ !$loop->last ? 'mb-32' : '' }}">

                {{-- Image --}}
                <div class="w-full md:w-4/6 overflow-hidden rounded-2xl">
                    <img
                        src="{{ $step['image'] }}"
                        alt="{{ $step['alt'] }}"
                        class="w-full scale-90 h-auto md:min-w-[100%] object-cover {{ $step['imagePosition'] === 'right' ? 'object-right' : 'object-left' }}"
                    />
                </div>

                {{-- Text --}}
                <div class="w-full md:w-2/6">
                    <p class="text-xs font-medium tracking-[0.3em] uppercase mb-3" style="font-family: 'Fira Code', monospace; color: #1a3c4a;">
                        {{ $step['label'] }}
                    </p>
                    <p class="text-gray-500 text-xl leading-relaxed">
                        {{ $step['text'] }}
                    </p>
                    @if (!empty($step['text2']))
                        <p class="text-gray-500 text-xl leading-relaxed mt-4">
                            {{ $step['text2'] }}
                        </p>
                    @endif
                </div>

            </div>
        @endforeach

    </div>
