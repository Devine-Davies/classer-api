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

<!-- Alpine.js must be included -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<body class="antialiased">
    @include('partials.shared.naviagtion')

    {{-- Hero --}}
    @include('partials.shared.hero', [
        'kicker' => 'Free Early Access',
        'title' => "Share your moments with Classer Essentials for free",
        'lead' => [
            'Experience a new way to turn your best moments into private, full-quality links. We’re opening early access to a small group of users. Want to be among the first to try it? Accept our invitation below.',
        ],
        'ctas' => [['label' => 'Accept Invite', 'href' => '#!inviteAccepted=true', 'variant' => 'primary']],
        'image' => [
            'src' => asset('assets/images/insiders/videoframe_1743.png'),
            'hoverSrc' => 'https://i.gifer.com/6Up.gif',
            'alt' => 'Skier jumping',
        ],
        'layers' => ['back' => '#fecaca88', 'front' => '#bfdbfe88'],
        'chips' => [
            [
                'type' => 'icon',
                'name' => 'share',
                'size' => 'lg',
                'classes' => 'w-8 h-8 fill-blue-500',
                'label' => 'Share',
            ],
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
            [
                'title' => 'Privacy-first',
                'body' =>
                    'No public links, no social uploads. <br/><br/> Just a private shareable link that auto-expires after 24 hours, so your moments stay yours.',
                'tone' => 'gold',
                'icon' => 'shield',
                'rot' => -10,
                'rotHover' => -5,
            ],
            [
                'title' => 'Keep it light',
                'body' =>
                    'Share more freely without leaving a permanent record. <br/><br/> Let others watch without downloads or taking up space.',
                'tone' => 'cream',
                'icon' => 'folder',
                'rot' => 0,
                'rotHover' => 0,
            ],
            [
                'title' => 'No account needed',
                'body' =>
                    'No account needed Just send the link. <br/><br/>  They can view your footage instantly, no signup, no app required.',
                'tone' => 'teal',
                'icon' => 'bolt',
                'rot' => 10,
                'rotHover' => 5,
            ],
        ],
    ])

    {{-- Early Tester Perks --}}
    @include('insiders.classer-share.early-tester-perks', [
        'title' => 'Exclusive perks for early testers',
        'subtitle' => 'Enjoy 3 months of free early access.',
        'perks' => [
            ['heading' => '3 months free — no credit card required'],
            ['heading' => 'Full-quality sharing (no compression)'],
            ['heading' => 'Private links that expire after 24 hours'],
            ['heading' => '100GB of cloud space', 'sub' => '≈25 hours of 1080p or 6 hours of 4K footage'],
        ],
        'ctaText' => 'Join as an Early Tester',
    ])

    {{-- Youtube Video Section, center --}}
    <section class="mx-auto max-w-7xl px-6 pb-14">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                See How it works
            </h2>
            <p class="mt-3 text-slate-600">
                Watch this quick demo to see how easy it's to share your action cam memories with Classer Essentials.
            </p>
        </div>
        <div class="mt-10 flex justify-center">
            <div class="w-full max-w-3xl aspect-video">
                <iframe class="w-full h-full rounded-lg shadow-lg" src="https://www.youtube.com/embed/J_fe2fMSFhg"
                    title="Classer Share Demo" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
        </div>
    </section>

    {{-- Cloud Share Success Modal --}}
    <article tabindex="-1" data-modal="cloud-share-success"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center backdrop-blur-md">
        <div class="relative p-4 m-auto w-1/1 max-w-lg ">
            <div class="relative p-4 bg-white rounded-lg shadow">
                <button type="button" data-modal-close
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                    @icon(close)
                    <span class="sr-only">Close</span>
                </button>
                <div class="py-6 px-6">
                    <h3 class="mb-0 text-center text-xl font-bold text-brand-color">
                        Congrats 🎉
                    </h3>
                    <p class="mb-6 w-full text-center">Your account has been upgraded to Classer Essentials. Open
                        Classer and enjoy private, high-quality cloud sharing, free during early access.</p>
                </div>
            </div>
        </div>
    </article>

    <section id="f-a-q-section">
        <div class="mx-auto max-w-7xl px-6 py-6">
            @include('partials.home.f-a-q', ['faqs' => [
                [
                    'q' => 'Is it really free? Why?',
                    'a' => 'Yes, you get 3 months completely free. All we ask in return is your feedback, so we can learn what works, what doesn’t, and how to make it better.',
                    'category' => 'General',
                ],
                [
                    'q' => 'How does it work?',
                    'a' => 'Pick a moment from your video and create a shareable clip. Then, go to your Moments section, copy the link, and share it with friends or family.',
                    'category' => 'Sharing Videos',
                ],
                [
                    'q' => 'Is it private?',
                    'a' => 'Yes, only the people you share the link with can view it. The video will automatically delete after 24 hours.',
                    'category' => 'Privacy',
                ],
                [
                    'q' => 'Does the person I send the video to need an account?',
                    'a' => 'No, they can watch your video straight from the link, no sign-up needed.',
                    'category' => 'Accessibility',
                ],
                [
                    'q' => 'Can they download the video?',
                    'a' => 'Yes, viewers can choose to stream it or download it.',
                    'category' => 'Viewing Options',
                ],
                [
                    'q' => 'Do viewers see the video in full quality?',
                    'a' => 'Yes, we keep your original resolution. No compression, no quality loss.',
                    'category' => 'Video Quality',
                ],
                [
                    'q' => 'Can I upload any type of video?',
                    'a' => 'No. Classer is designed for personal and adventure moments only. Uploading explicit, harmful, or illegal content is not allowed and may result in account removal.',
                    'category' => 'Content Policy',
                ],
                [
                    'q' => 'Who are you again?',
                    'a' => 'We’re two people based in Wales, UK, who got tired of the chaos of managing our action cam footage, so we built something to fix it.',
                    'category' => 'About Us',
                ],
                [
                    'q' => 'How can I contact you?',
                    'a' => 'If you have any questions, drop us an email at contact@classermedia.com',
                    'category' => 'Support',
                ]
            ]])
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

<script>
    (() => {
        // Coalesce all URL-change signals into one event, and ignore no-op changes.
        let lastUrl = null; // track the last URL we handled
        let lastInviteAccepted = null; // track the last value we acted on
        let scheduled = false; // microtask-based debounce

        const parseHashParams = () => {
            let h = window.location.hash || '';
            if (h.startsWith('#')) h = h.slice(1);
            if (h.startsWith('!')) h = h.slice(1); // support "#!..."
            if (h.startsWith('?')) h = h.slice(1);
            const params = new URLSearchParams(h);
            return Object.fromEntries(params.entries());
        };

        const handleUrlChange = () => {
            const href = window.location.href;
            if (href === lastUrl) return; // ignore duplicates
            lastUrl = href;

            const {
                inviteAccepted
            } = parseHashParams();
            const current = inviteAccepted === 'true';

            // Only act when the value actually toggles
            if (current !== lastInviteAccepted) {
                lastInviteAccepted = current;
                if (current) {
                    const params = new URLSearchParams(window.location.search);
                    const email = params.get('email'); // "someEmail"

                    if (!email) {
                        console.warn('Invite acceptance attempted but no email found in URL parameters.');
                        return;
                    }

                    postInviteAccepted({
                        accepted: true,
                        email,
                        timestamp: new Date().toISOString(),
                    }).then((response) => {
                        displaySuccessModal('cloud-share-success');
                    }).catch((error) => {
                        alert(
                            'An error occurred while processing your request. Please try again later.'
                        );
                    });
                }
            }
        };

        const emitLocationChange = () => {
            if (scheduled) return; // coalesce bursts (e.g., pushState then hashchange)
            scheduled = true;
            queueMicrotask(() => {
                scheduled = false;
                window.dispatchEvent(new Event('locationchange'));
            });
        };

        // Listen once, in one place
        window.addEventListener('locationchange', handleUrlChange);

        // Patch history to emit our unified event
        const _pushState = history.pushState;
        history.pushState = function(...args) {
            const ret = _pushState.apply(this, args);
            emitLocationChange();
            return ret;
        };

        // Patch replaceState similarly
        const _replaceState = history.replaceState;
        history.replaceState = function(...args) {
            const ret = _replaceState.apply(this, args);
            emitLocationChange();
            return ret;
        };

        // Native events funnel into our unified event
        window.addEventListener('popstate', emitLocationChange);
        window.addEventListener('hashchange', emitLocationChange);

        // Kick off once on load
        emitLocationChange();
    })();

    // POST invite acceptance to the server
    const postInviteAccepted = (payload) => {
        return fetch('/api/insiders/invite/accept', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin', // include cookies if same origin
            body: JSON.stringify(payload),
        });
    };

    // Display modals based on URL parameters
    const displaySuccessModal = (target) => {
        const modal = document.querySelector(`[data-modal="${target}"]`);
        modal && modal.classList.toggle("hidden");
    }
</script>
