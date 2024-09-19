@php
    $logosImgPaths = [
        'akaso' => asset('/assets/images/welcome/logos/akaso.png'),
        'sjcam' => asset('/assets/images/welcome/logos/sjcam.png'),
        'dji' => asset('/assets/images/welcome/logos/dji.png'),
        'go-pro' => asset('/assets/images/welcome/logos/go-pro.png'),
        'insta360' => asset('/assets/images/welcome/logos/insta360.png'),
        'nikon' => asset('/assets/images/welcome/logos/nikon.png'),
        'veho' => asset('/assets/images/welcome/logos/veho.png'),
    ];
@endphp

<h2 class="text-2xl font-bold text-center text-brand-color mb-6 md:mb-8">
    Works with your favourite brands
</h2>

<div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-7">
    @foreach ($logosImgPaths as $logoName => $logoImgPath)
        <div class="h-16 flex align-center justify-center">
            <img class="m-auto w-6/12" src="{{ $logoImgPath }}" alt="{{ ucfirst($logoName) }} logo" />
        </div>
    @endforeach
</div>
