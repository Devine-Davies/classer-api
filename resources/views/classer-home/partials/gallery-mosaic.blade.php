{{-- Gallery Mosaic Section --}}

{{-- Section Heading --}}
<div class="text-center pt-16 md:pt-24 pb-12 md:pb-16 px-8">
    <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight mb-6 text-brand-color">
        Browse your entire adventure history in one place
    </h2>
    <p class="text-gray-400 text-xl leading-relaxed max-w-xl mx-auto">
        Classer imports your footage to your external hard drive and turns raw files into meaningful collections.
        Your files stay yours. Just structured, searchable, and ready to relive.
    </p>
</div>

<div class="overflow-hidden" style="height: 80vh; min-height: 500px; max-height: 1200px;">

    @php
        // ── Column speed variables (animation duration in seconds) ──
        // Lower = faster scroll. Adjust these to taste.
        $colSpeeds = [58, 50, 64, 54];

        // ── Gallery data: 4 columns, each with images + optional titles ──
        $gallery = [
            // Column 1
            [
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/ballon.png'), 'title' => 'Ballon Festival', 'tag' => 'Summer 2024'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/beach.png'), 'title' => 'Beach', 'tag' => 'Winter 2023'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/Biking.png'), 'title' => 'Biking', 'tag' => 'Autumn 2022'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/car.png'), 'title' => 'Adventures', 'tag' => 'Spring 2025'],
            ],
            // Column 2
            [
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/dog.png'), 'title' => 'My Dog', 'tag' => 'Summer 2023'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/drive.png'), 'title' => 'Road Trip', 'tag' => 'Winter 2024'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/family.png'), 'title' => 'Family', 'tag' => 'Spring 2022'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/kayaking.png'), 'title' => 'Kayaking', 'tag' => 'Autumn 2025'],
            ],
            // Column 3
            [
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/mtb.png'), 'title' => 'MTB', 'tag' => 'Winter 2022'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/People-skates.png'), 'title' => 'Friendship', 'tag' => 'Summer 2025'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/People-viewing.png'), 'title' => 'Chilling', 'tag' => 'Spring 2023'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/skating.png'), 'title' => 'Skating', 'tag' => 'Autumn 2024'],
            ],
            // Column 4
            [
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/smile.png'), 'title' => 'Key Moments', 'tag' => 'Winter 2024'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/smile-glasses.png'), 'title' => 'Me Time', 'tag' => 'Summer 2023'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/Snow.png'), 'title' => 'Snowboarding', 'tag' => 'Autumn 2022'],
                ['src' => asset('/assets/images/classer-home/mosaic-gallery/Underwater.png'), 'title' => 'Underwater', 'tag' => 'Spring 2025'],
            ],
        ];
    @endphp
    {{-- Inline CSS — only for animations & masks that Tailwind can't handle --}}
    <style>
        .gallery-mosaic {
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 25%, black 75%, transparent 100%);
            mask-image: linear-gradient(to bottom, transparent 0%, black 25%, black 75%, transparent 100%);
        }

        .gallery-column-inner {
            animation-timing-function: linear;
            animation-iteration-count: infinite;
            animation-name: scrollUp;
        }

        @keyframes scrollUp {
            0%   { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        @keyframes fadeIn {
            0%   { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Per-column speed & per-card stagger */
        @foreach ($gallery as $colIdx => $col)
            .gallery-column-inner-{{ $colIdx }} {
                animation-duration: {{ $colSpeeds[$colIdx] }}s;
            }
            @foreach ($col as $cardIdx => $card)
                .gallery-card-{{ $colIdx }}-{{ $cardIdx }} {
                    animation-delay: {{ ($colIdx * 0.15) + ($cardIdx * 0.25) }}s;
                }
            @endforeach
        @endforeach

    </style>

    <div class="gallery-mosaic grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 p-3 h-full overflow-hidden">
        @foreach ($gallery as $colIdx => $column)
            <div class="gallery-column flex flex-col gap-3 {{ $colIdx === 0 ? 'flex' : '' }} {{ $colIdx === 1 ? 'hidden md:flex' : '' }} {{ $colIdx === 2 ? 'hidden xl:flex' : '' }} {{ $colIdx === 3 ? 'hidden 2xl:flex' : '' }}">
                <div class="gallery-column-inner flex flex-col gap-3 gallery-column-inner-{{ $colIdx }}">

                    {{-- Render images twice so the scroll loops seamlessly --}}
                    @for ($repeat = 0; $repeat < 2; $repeat++)
                        @foreach ($column as $cardIdx => $card)
                            <div class="gallery-card gallery-card-{{ $colIdx }}-{{ $cardIdx }} relative rounded-xl overflow-hidden flex-shrink-0 opacity-0" style="animation: fadeIn 1.2s ease forwards;">
                                <img src="{{ $card['src'] }}" alt="{{ $card['title'] ?: 'Gallery image' }}" loading="lazy" class="w-full h-auto block object-cover" />
                                @if (!empty($card['title']) || !empty($card['tag']))
                                    <div class="absolute bottom-0 inset-x-0 flex flex-col gap-0.5 px-3.5 py-3 bg-gradient-to-t from-black/50 to-transparent text-white">
                                        @if (!empty($card['tag']))
                                            <p class="text-sm font-medium tracking-widest" >{{ $card['tag'] }}</p>
                                        @endif
                                        @if (!empty($card['title']))
                                            <p class="text-4xl font-medium tracking-tight">{{ $card['title'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endfor

                </div>
            </div>
        @endforeach
    </div>

</div>
