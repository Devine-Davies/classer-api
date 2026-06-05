{{-- Problem: Most adventures disappear into hard drives --}}
@php
    $problemCards = [
        [
            'img'   => 'https://placeholders.io/600/400',
            'alt'   => 'Laptop with footage being copied to drive',
            'label' => 'They get copied to a drive and left there',
        ],
        [
            'img'   => 'https://placeholders.io/600/400',
            'alt'   => 'Folder listing with cryptic file names',
            'label' => 'Folders multiply and difficult to organise them',
        ],
        [
            'img'   => 'https://placeholders.io/600/400',
            'alt'   => 'Drawer full of SD cards',
            'label' => 'The moments you love get forgotten',
        ],
    ];
@endphp

<div class="mx-auto w-full max-w-6xl">

    {{-- Section Heading --}}
    <h2 class="text-2xl md:text-4xl lg:text-5xl m-auto max-w-3xl leading-tight text-center mb-4 text-brand-color">
        Most adventures disappear<br>
        into hard drives
    </h2>

    <p class="text-gray-400 text-base md:text-lg leading-relaxed max-w-xl mx-auto text-center mb-12 md:mb-16">
        After the rush of capturing comes the drop. Footage gets buried, forgotten, and never seen again.
    </p>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
        @foreach ($problemCards as $card)
            <div class="flex flex-col rounded-3xl border border-[#e2e2e2] overflow-hidden bg-white">
                <div class="pb-0">
                    <div class="overflow-hidden rounded-2xl">
                        <img
                            src="{{ $card['img'] }}"
                            alt="{{ $card['alt'] }}"
                            class="w-full h-[260px] md:h-[260px] lg:h-[280px] object-cover block"
                        />
                    </div>
                </div>
                <div class="bg-[#f4f4f4] w-full mt-0">
                    <p class="text-center text-brand-color font-semibold text-base md:text-lg leading-snug px-6 pb-8 pt-5">
                        {{ $card['label'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

</div>
