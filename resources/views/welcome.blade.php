<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - The essential accessory for your action camera & drones</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')

    <section id="hero-section">
        <div class="hero-bg" >
            @include('partials.home.hero')
        </div>
    </section>

    <section id="features-section" class="bg-off-white" >
        <div class="mx-auto w-full max-w-7xl p-8 pb-24 md:pt-16 overflow-hidden">
            @include('partials.home.features')
        </div>
    </section>

    <section id="made-for-all-section" class="bg-badge">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.made-for-all')
        </div>
    </section>

    <section id="how-it-works-section" class="bg-off-white" >
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.how-it-works')
            <div class="mt-12 my-8">
            @include('partials.home.vendors')
            </div>
        </div>
    </section>

    <section id="not-just-a-tool-section" class="hidden">
        @include('partials.home.not-just-a-tool')
    </section>

    <section id="pricing-models-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.pricing-models')
        </div>
    </section>

    <section id="insights-section">
        <div class="mx-auto max-w-8xl px-6 py-6 md:py-12">
            @include('partials.home.insights')
        </div>
    </section>

    <section id="environmental-section" class="hidden" >
        <div>
            @include('partials.home.environmental')
        </div>
    </section>

    <section id="join-our-community-section" class="bg-off-white">
        <div>
            @include('partials.home.join-our-community')
        </div>
    </section>

    <section id="f-a-q-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.f-a-q', ['faqs' => [
                [
                    'q' => 'Is it for mobile?',
                    'a' => 'We are currently focusing on desktop, but with future plans to make it work for mobile too.',
                    'category' => 'Platforms & Devices',
                ],
                [
                    'q' => 'Can I cut and trim my videos?',
                    'a' =>
                        'Yes, Classer allows you to cut and trim your videos reducing file size so that they can be easily shared with other services.',
                    'category' => 'Editing Features',
                ],
                [
                    'q' => 'Is this a cloud service?',
                    'a' => 'Not yet but we are working on it ;).',
                    'category' => 'Cloud & Sync',
                ],
                [
                    'q' => 'Does Classer use my directory from my folder file?',
                    'a' =>
                        'Yes, Classer leverages the existing structure of your file folder, allowing you to get quickly onboarded and enabling faster access to what you\'re seeking.',
                    'category' => 'File Management',
                ],
                [
                    'q' => 'Does it work with all action cameras?',
                    'a' => 'Yes and all video file formats, including .mp4, .mov, .avi',
                    'category' => 'Compatibility',
                ],
                [
                    'q' => 'I would like to contact the team, how do I do it?',
                    'a' => 'Happy to chat! Please contact us at contact@classermedia.com',
                    'category' => 'Support',
                ],
                [
                    'q' => 'How to turn on my GPS on my GoPro?',
                    'a' =>
                        'From the main screen from GoPro, swipe down (HERO11/10/9 white, swipe left after swiping down) and tap [Preferences]. For HERO11 Black, scroll to [GPS] and turn GPS [On]. For HERO10/9 Black, scroll to [Regional], tap [GPS] and turn GPS [On].',
                    'category' => 'How-to',
                ],
            ]])
        </div>
    </section>

    <section id="banner-section">
        @include('partials.home.banner')
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
