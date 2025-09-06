@php
    $listItem = function ($label) {
        return <<<HTML
            <li class="flex items-start gap-x-2">
                <svg class="star-icon-color w-6 h-6 " xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="28"><path d="M10 16.207l-6.173 3.246 1.179-6.874L.01 7.71l6.902-1.003L10 .453l3.087 6.254 6.902 1.003-4.995 4.869 1.18 6.874z"></path></svg>
                <span>$label</span>
            </li>
        HTML;
    };
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Share Early Access</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased">
    @include('partials.shared.naviagtion')

    {{-- Hero --}}
    @include('partials.shared.hero', [
        'kicker' => 'Free Early Access',
        'title' => "Share your moments with \n Classer Share for free",
        'lead' => [
            'A grate new way to share to turn your best moments into private, full-quality links. We’re currently giving early access for this feature to selected users. Want to be one of them to try it? <a href="mailto:info@classermedia.com" class="underline font-semibold">Sign up now</a>.',
        ],
        'image' => ['src' => asset('assets/images/insiders/videoframe_1743.png'), 'hoverSrc' => 'https://i.gifer.com/6Up.gif', 'alt' => 'Skier jumping'],
        'layers' => ['back' => '#fecaca88', 'front' => '#bfdbfe88'],
        'chips' => [
            ['type' => 'icon', 'name' => 'share', 'size' => 'lg', 'classes' => 'w-8 h-8 fill-blue-500', 'label' => 'Share'],
            ['type' => 'icon', 'name' => 'hashTag', 'classes' => 'w-6 h-6'],
            ['type' => 'icon', 'name' => 'heart', 'classes' => 'text-rose-500 w-6 h-6'],
            ['type' => 'dot', 'classes' => 'bg-yellow-500'],
        ],
        'toast' => null, // set to null to hide
        'height' => '22rem', // optional
        'wiggle' => true, // CSS parallax wiggle on hover
    ])

    {{-- Features --}}
    @include('insiders.classer-share.features', [
        'eyebrow' => 'New features!',
        'items' => [
            ['title'=>'Privacy-first','body'=>'No public links, no social uploads. <br/><br/> Just a private shareable link that auto-expires after 24 hours, so your moments stay yours.','tone'=>'gold','icon'=>'shield','rot'=>-10,'rotHover'=>-5],
            ['title'=>'Keep it light','body'=>'Share more freely without leaving a permanent record. <br/><br/> Let others watch without downloads or taking up space.','tone'=>'cream','icon'=>'folder','rot'=>0,'rotHover'=>0],
            ['title'=>'No account needed','body'=>'No account needed Just send the link. <br/><br/>  They can view your footage instantly, no signup, no app required.','tone'=>'teal','icon'=>'bolt','rot'=>10,'rotHover'=>5],
        ],
    ])

    {{-- Early Tester Perks --}}
    @include('insiders.classer-share.early-tester-perks', [
        'title' => "Exclusive Perks for Early Testers",
        'subtitle' => "Try it free for 3 months, no credit card required.",
        'perks' => [
            ['heading' => '3 months free — no credit card required'],
            ['heading' => 'Full-quality sharing (no compression)'],
            ['heading' => 'Private links that expire after 24 hours'],
            ['heading' => '100GB of cloud space', 'sub' => '≈25 hours of 1080p or 6 hours of 4K footage'],
        ],
        'ctaText' => 'Join as an Early Tester',
        {{-- 'ctaHref' => route('early-access.signup'), --}}
    ])

    {{-- Youtube Video Section, center --}}
    <section class="mx-auto max-w-7xl px-6 pb-14">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                See How it works
            </h2>
            <p class="mt-3 text-slate-600">
                Watch this quick demo to see how easy it is to share your action cam memories with Classer Share.
            </p>
        </div>
        <div class="mt-10 flex justify-center">
            <div class="w-full max-w-3xl aspect-video">
                <iframe class="w-full h-full rounded-lg shadow-lg" src="https://www.youtube.com/embed/J_fe2fMSFhg" title="Classer Share Demo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
