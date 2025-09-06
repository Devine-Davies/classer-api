{{-- Features section (3 tilted cards) --}}
{{-- Optional props:
  $eyebrow (string), $title (string), $items (array of 3)
  item = ['title','body','tone: gold|cream|teal','icon: shield|folder|bolt','rot','rotHover']
--}}

@php
    $eyebrow = $eyebrow ?? 'New feature!';
    $title = $title ?? 'Share your action cam memories instantly';
    $items = $items ?? [
        [
            'title' => 'Privacy-first',
            'body' =>
                'No public links, no social uploads. A private shareable link that auto-expires after 24 hours—your moments stay yours.',
            'tone' => 'gold',
            'icon' => 'shield',
            'rot' => -8,
            'rotHover' => -4,
        ],
        [
            'title' => 'Keep it light',
            'body' =>
                'Share more freely without leaving a permanent record. Let others watch without downloads or taking up space.',
            'tone' => 'cream',
            'icon' => 'folder',
            'rot' => 0,
            'rotHover' => 0,
        ],
        [
            'title' => 'No account needed',
            'body' => 'Just send the link. They can view instantly—no signup, no app required.',
            'tone' => 'teal',
            'icon' => 'bolt',
            'rot' => 8,
            'rotHover' => 4,
        ],
    ];

    $icon = function (string $name, bool $invert = false): string {
        $color = $invert ? '#ffffff' : '#0f3b47';
        switch ($name) {
            case 'shield':
                return '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="' .
                    $color .
                    '"><path d="M12 2l7 3v6c0 5-3.5 9.5-7 11-3.5-1.5-7-6-7-11V5l7-3zm0 3.1L7 6.9V11c0 3.7 2.6 7.4 5 8.9 2.4-1.5 5-5.2 5-8.9V6.9l-5-1.8z"/></svg>';
            case 'folder':
                return '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="' .
                    $color .
                    '"><path d="M10 4l2 2h8a2 2 0 012 2v9a3 3 0 01-3 3H5a3 3 0 01-3-3V7a3 3 0 013-3h5z"/><path fill="#ffffff" opacity=".9" d="M14.5 11.2l-1.35.71.26-1.51-1.1-1.07 1.53-.22.68-1.38.68 1.38 1.53.22-1.1 1.07.26 1.51z"/></svg>';
            case 'bolt':
                return '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="' .
                    $color .
                    '"><path d="M13 2L3 14h6l-1 8 10-12h-6l1-8z"/></svg>';
        }
        return '';
    };
@endphp


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('#features .cards .card-slide');
        if (!cards.length || !('IntersectionObserver' in window)) return;

        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;

                const el = entry.target;
                el.classList.remove('opacity-0', 'translate-y-4');
                el.classList.add('opacity-100', 'translate-y-0');
                // obs.unobserve(el); // run once per card
            });
        }, {
            threshold: 1,
            // root: document.querySelector('#features .cards'), // <-- use this IF .cards is a scroll container
            // rootMargin: '0px 0px -10% 0px', // <-- optional: trigger a bit earlier
        });

        cards.forEach(el => io.observe(el));
    });
</script>

<section id="features" class="features">
    <div class="container">
        <p class="eyebrow">
            <span class="star" aria-hidden="true">@icon(star)</span> {{ $eyebrow }}
        </p>
        <h2 id="features-title" class="title">{{ $title }}</h2>

        <div class="cards">
            @foreach ($items as $it)
                @php
                    $tone = $it['tone'] ?? 'cream';
                    $rot = $it['rot'] ?? 0;
                    $rotH = $it['rotHover'] ?? $rot;
                    $invert = $tone === 'teal';
                @endphp
                <article class="card card--{{ $tone }}"
                    style="--rot: {{ $rot }}deg; --rot-hover: {{ $rotH }}deg;">
                    <div class="card__icon">{!! $icon($it['icon'] ?? 'shield', $invert) !!}</div>
                    <h3 class="card__title">{{ $it['title'] ?? '' }}</h3>
                    <p class="card__body">{!! $it['body'] ?? '' !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>


<style>
    :root {
        --bg: #f8fafc;
        --text: #0f172a;
        --muted: #334155;
        --teal: #0b3b46;
        --teal-ink: #ffffff;
        --gold: #f0c86b;
        --cream: #f4f3ee;
        --radius: 1rem;
        --ring: 0 0 0 1px rgba(0, 0, 0, .06);
        --shadow: 0 18px 40px rgba(2, 6, 23, .15);
        --shadow-soft: 0 10px 24px rgba(2, 6, 23, .1);
        --ease: cubic-bezier(.2, .8, .2, 1);
        --dur: 420ms;
    }

    .features {
        padding: 4.5rem 0 14rem;
        background: var(--bg);
        color: var(--text);
        text-align: center
    }

    .features .container {
        max-width: 72rem;
        margin: 0 auto;
        padding: 0 1.25rem
    }

    .features .eyebrow {
        display: inline-flex;
        justify-content: center;
        color: #0ea5e9;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }

    .features .star {
        margin-right: .35rem
    }

    .features .title {
        font-size: clamp(1.5rem, 1.4rem + 1.8vw, 2.5rem);
        line-height: 1.15;
        font-weight: 800;
        margin: 0 auto 2.5rem;
        max-width: 34ch
    }

    .features .cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        justify-items: center
    }

    @media (min-width:900px) {
        .features .cards {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem
        }
    }

    .features .card {
        width: min(22rem, 92%);
        border-radius: var(--radius);
        box-shadow: var(--ring), var(--shadow-soft);
        padding: 1.35rem 1.25rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
        transition: all;
        will-change: transform position: relative;
        z-index: 1;
    }

    .features .card:nth-child(1) {
        z-index: 0;
        transform: rotate(var(--rot, 0deg)) translateZ(0) translateX(20px) translateY(75px)
    }

    .features .card:nth-child(2) {
        transform: rotate(var(--rot, 0deg)) translateZ(0) translateX(0) translateY(50px) scale(1.2);
    }

    .features .card:nth-child(3) {
        z-index: 0;
        transform: rotate(var(--rot, 0deg)) translateZ(0) translateX(-20px) translateY(75px);
    }

    .features .card__icon {
        display: grid;
        place-items: center;
        height: 3.25rem;
        width: 3.25rem;
        border-radius: .8rem;
        margin: .5rem auto 1rem;
        background: rgba(255, 255, 255, .8)
    }

    .features .ico {
        height: 1.8rem;
        width: 1.8rem;
        display: block
    }

    .features .card__title {
        font-size: 1.25rem;
        line-height: 1.2;
        margin: .25rem 0 .65rem;
        font-weight: 800
    }

    .features .card__body {
        color: #274452;
        opacity: .9;
        line-height: 1.45;
        max-width: 30ch;
        margin: 0
    }

    .features .card--gold {
        background: var(--gold);
        color: #0f3b47
    }

    .features .card--gold .card__icon {
        background: rgba(255, 255, 255, .55)
    }

    .features .card--cream {
        background: var(--cream);
        color: #0f3b47
    }

    .features .card--cream .card__icon {
        background: rgba(11, 59, 70, .08)
    }

    .features .card--teal {
        background: var(--teal);
        color: var(--teal-ink)
    }

    .features .card--teal .card__icon {
        background: rgba(255, 255, 255, .12)
    }

    .features .card--teal .card__body {
        color: #ffffff;
        opacity: .9
    }
</style>
