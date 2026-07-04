@php
    $logos = [
        'akaso' => [
            'src' => asset('/assets/images/welcome/logos/akaso.png'),
            'containerClass' => '',
            'class' => 'w-6/12',
        ],
        'sjcam' => [
            'src' => asset('/assets/images/welcome/logos/sjcam.png'),
            'containerClass' => '',
            'class' => 'w-6/12',
        ],
        'dji' => [
            'src' => asset('/assets/images/welcome/logos/dji.png'),
            'containerClass' => '',
            'class' => 'w-5/12',
        ],
        'go-pro' => [
            'src' => asset('/assets/images/welcome/logos/go-pro.png'),
            'containerClass' => '',
            'class' => 'w-7/12',
        ],
        'insta360' => [
            'src' => asset('/assets/images/welcome/logos/insta360.png'),
            'containerClass' => '',
            'class' => 'w-7/12',
        ],
        'nikon' => [
            'src' => asset('/assets/images/welcome/logos/nikon.png'),
            'containerClass' => '',
            'class' => 'w-5/12',
        ],
        'veho' => [
            'src' => asset('/assets/images/welcome/logos/veho.png'),
            'containerClass' => 'hidden md:flex',
            'class' => 'w-6/12',
        ],
        'veho-temp' => [
            'src' => asset('/assets/images/welcome/logos/veho.png'),
            'containerClass' => 'hidden md:flex lg:hidden',
            'class' => 'w-6/12',
        ],
    ];
@endphp

<section>
    <header>
        <h2 class="text-xl md:text-2xl text-center text-brand-color mb-4">
            Works with your favourite brands
        </h2>
    </header>

    <section class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-7">
        @foreach ($logos as $logoName => $logo)
            <div class="flex {{ $logo['containerClass'] }} items-center justify-center space-y-4 md:space-y-6 lg:space-y-8 py-4 md:py-6 lg:py-8">
                <img
                    src="{{ $logo['src'] }}"
                    class="m-auto {{ $logo['class'] }}"
                    alt="{{ ucfirst($logoName) }} logo"
                />
            </div>
        @endforeach
    </section>
</section>