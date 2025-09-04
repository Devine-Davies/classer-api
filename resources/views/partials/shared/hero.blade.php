{{-- Configurable Hero (Blade view) --}}
{{-- Expects (all optional):
  $kicker, $title, $lead (string|array), $ctas ([{label,href,variant}]),
  $image ({src,alt}), $layers ({back,front}), $chips ([{type, name|color, size}]),
  $toast ({title,button}|null), $height (css size), $wiggle (bool)
--}}

@php
    $kicker = $kicker ?? null;
    $title = $title ?? "Try our upcoming\nsharing feature";
    $lead = $lead ?? [
        // 'Classer Share is a new way to turn your best moments into private, full-quality links.',
        // "We're currently giving early access for this feature to selected users. Want to be one of them to try it? Reach out below.",
    ];
    $ctas =
        $ctas ??
        [
            // ['label' => 'Request early access', 'href' => '#', 'variant' => 'primary'],
            // ['label' => 'Learn more', 'href' => '#', 'variant' => 'ghost'],
        ];
    $image = $image ?? ['src' => asset('images/hero-ski.png'), 'alt' => 'Skier midair with snow splash'];
    $layers = $layers ?? ['back' => 'rgba(244,114,182,.55)', 'front' => 'rgba(56,189,248,.55)'];
    $chips =
        $chips ??
        [
            // ['type' => 'icon', 'name' => 'link', 'size' => 'lg'],
            // ['type' => 'icon', 'name' => 'cog'],
            // ['type' => 'dot', 'color' => 'green'],
            // ['type' => 'icon', 'name' => 'heart'],
        ];
    $height = $height ?? '18rem';
    $wiggle = array_key_exists('wiggle', get_defined_vars()) ? (bool) $wiggle : true;
    $toast = array_key_exists('toast', get_defined_vars())
        ? $toast
        : ['title' => 'Shared link has been created', 'button' => 'Copy link'];

    $leads = is_array($lead) ? $lead : (is_string($lead) ? [$lead] : []);
    $styleVars = sprintf('--hero-h:%s;--layer1:%s;--layer2:%s;', e($height), e($layers['back']), e($layers['front']));

    // tiny SVG helper
    $icon = function (string $name, $classers): string {
        switch ($name) {
            case 'link':
                return '<svg class="' .
                    $classers .
                    '" xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 6a4.5 4.5 0 10-6.364 6.364l2 2M10.5 18a4.5 4.5 0 006.364-6.364l-2-2"/></svg>';
            case 'cog':
                return '<svg class="' .
                    $classers .
                    '"  xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M11.983 1.954a1 1 0 00-1.966 0l-.179 1.43a7.034 7.034 0 00-1.457.607l-1.287-.743a1 1 0 00-1.366.366L3.71 5.03a1 1 0 00.366 1.366l1.287.743a7.034 7.034 0 000 1.214L4.076 9.1a1 1 0 00-.366 1.366l1.018 1.766a1 1 0 001.366.366l1.287-.743c.46.26.948.47 1.457.607l.179 1.43a1 1 0 001.966 0l.179-1.43c.509-.137.997-.347 1.457-.607l1.287.743a1 1 0 001.366-.366l1.018-1.766A1 1 0 0016.29 9.1l-1.287-.743c.037-.402.037-.812 0-1.214l1.287-.743a1 1 0 00.366-1.366L15.64 3.614a1 1 0 00-1.366-.366l-1.287.743a7.034 7.034 0 00-1.457-.607l-.179-1.43zM10 12.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>';
            case 'heart':
                return '<svg class="' .
                    $classers .
                    '"   xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-1.054-.649 25.18 25.18 0 01-4.244-3.17C3.688 15.356 2 13.24 2 10.75 2 8.264 3.988 6.5 6.318 6.5c1.31 0 2.636.561 3.682 1.61L12 10.118l2-1.999c1.07-1.07 2.37-1.62 3.682-1.62C20.012 6.5 22 8.264 22 10.75c0 2.49-1.688 4.606-4.318 6.326a25.23 25.23 0 01-4.244 3.17 15.275 15.275 0 01-1.047.644l-.022.012-.007.003a.75.75 0 01-.717 0z"/></svg>';

            case 'share':
                return '<svg class="' .
                    $classers .
                    '" xmlns="http://www.w3.org/2000/svg" class="icon" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 500 511.61"><path fill-rule="nonzero" d="m265.96 363.22 15.5-101.27c-45.53 4.53-96.07 15.77-138.72 45.89-47.72 33.69-86.32 91.71-98.25 191.8-.87 7.43-7.62 12.75-15.06 11.87-5.73-.68-10.21-4.86-11.55-10.14C7 468.76 1.42 437.95.25 409.03c-3.27-79.4 26.39-144.22 70.18-193.61 43.36-48.92 100.66-82.64 153.32-100.33 20.18-6.8 39.79-11.27 57.77-13.36L266.08 15.9c-1.32-7.34 3.57-14.38 10.91-15.69 4.07-.72 8.04.46 11 2.9l207.1 171.3c5.76 4.77 6.57 13.33 1.8 19.08l-1.54 1.59-207.06 180.39c-5.64 4.92-14.22 4.32-19.14-1.32a13.529 13.529 0 0 1-3.19-10.93z"/></svg>';

            case 'hashTag':
                return '<svg class="' .
                    $classers .
                    ' xmlns="http://www.w3.org/2000/svg" viewBox="-5 -5 24 24" preserveAspectRatio="xMinYMin" ><path d="M6 6v2h2V6H6zm0-2h2V1a1 1 0 1 1 2 0v3h3a1 1 0 0 1 0 2h-3v2h3a1 1 0 0 1 0 2h-3v3a1 1 0 0 1-2 0v-3H6v3a1 1 0 0 1-2 0v-3H1a1 1 0 1 1 0-2h3V6H1a1 1 0 1 1 0-2h3V1a1 1 0 1 1 2 0v3z"></path></svg>';
        }
        return '';
    };
@endphp

<section id="share-hero" class="hero" data-wiggle="{{ $wiggle ? 'true' : 'false' }}" style="{{ $styleVars }}"
    aria-labelledby="hero-title">

    <div class="container">
        <div class="grid">
            <div class="copy">
                @if ($kicker)
                    <p class="kicker text-sm px-3 py-1 rounded-lg mb-1 bg-amber-300 inline-flex ">{{ $kicker }}</p>
                @endif
                <h1 id="hero-title" class="title">{!! nl2br(e($title)) !!}</h1>

                @foreach ($leads as $p)
                    <p class="lead">{{ $p }}</p>
                @endforeach

                @if (!empty($ctas))
                    <div class="actions">
                        @foreach ($ctas as $btn)
                            @php
                                $variant = $btn['variant'] ?? 'primary';
                                $cls = $variant === 'ghost' ? 'btn btn--ghost' : 'btn text-lg';
                            @endphp
                            <a class="{{ $cls }}"
                                href="{{ $btn['href'] ?? '#' }}">{{ $btn['label'] ?? 'Click' }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="visual" aria-hidden="true">
                <div class="layer layer--rose" style="background: var(--layer1)"></div>
                <div class="layer layer--sky" style="background: var(--layer2)"></div>

                <figure class="card">
                    <img src="{{ $image['src'] ?? '' }}" alt="{{ $image['alt'] ?? '' }}">

                    @if ($toast)
                        <figcaption class="toast">
                            <div class="toast__inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 3H5a2 2 0 00-2 2v12a2 2 0 002 2h11m3-4V5a2 2 0 00-2-2h-1m3 12l-5 5m5-5h-3a2 2 0 01-2-2v-3" />
                                </svg>
                                <div>
                                    <p class="toast__title">{{ $toast['title'] ?? '' }}</p>
                                    @if (!empty($toast['button']))
                                        <button type="button" class="toast__btn">{{ $toast['button'] }}</button>
                                    @endif
                                </div>
                            </div>
                        </figcaption>
                    @endif
                </figure>

                @if (!empty($chips))
                    <div class="chips">
                        @foreach ($chips as $c)
                            @php
                                $isLg = ($c['size'] ?? null) === 'lg';
                                $classes = 'chip' . ($isLg ? ' chip--lg' : '');
                            @endphp
                            <div class="{{ $classes }}">
                                @if (($c['type'] ?? '') === 'dot')
                                    @php $color = $c['color'] ?? 'green'; @endphp
                                    <span class="dot {{ $c['classes'] }}"></span>
                                @elseif(($c['type'] ?? '') === 'icon')
                                    {!! $icon($c['name'] ?? '', $c['classes'] ?? '') !!}
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>


<style>
    :root {
        --hero-radius: 1.25rem;
        --hero-h: 18rem;
        --ring: 0 0 0 1px rgba(0, 0, 0, 0.06);
        --shadow: 0 20px 45px rgba(2, 6, 23, 0.16);
        --shadow-soft: 0 10px 25px rgba(2, 6, 23, 0.1);
        --ease: cubic-bezier(0.2, 0.8, 0.2, 1);
        --dur: 420ms;
        --bg: #f8fafc;
        /* slate-50 */
        --text: #0f172a;
        /* slate-900 */
        --muted: #475569;
        /* slate-600 */
    }

    .hero {
        padding-block: 2rem;
        margin: 6rem 0;
    }

    .hero .container {
        max-width: 80rem;
        margin-inline: auto;
        padding-inline: 1.5rem;
    }

    .hero .grid {
        display: grid;
        gap: 3rem;
        align-items: center;
        grid-template-columns: 1fr;
    }

    @media (min-width: 1024px) {
        .hero .grid {
            grid-template-columns: 1.1fr 1fr;
        }
    }

    .hero .title {
        font-weight: 800;
        line-height: 1.08;
        font-size: clamp(2rem, 3.5vw + 1rem, 3.25rem);
        letter-spacing: -0.02em;
    }

    .hero .lead {
        margin-top: 1rem;
        color: var(--muted);
        {{-- max-width: 56ch; --}} max-width: 90%;
    }

    .hero .lead+.lead {
        margin-top: 0.5rem;
    }

    .hero .actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .hero .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 1.1rem;
        border-radius: 0.75rem;
        text-decoration: none;
        font-weight: 600;
        transition: background var(--dur) var(--ease),
            transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
    }

    .hero .btn--primary {
        background: #0f172a;
        color: #fff;
        box-shadow: var(--shadow-soft);
    }

    .hero .btn--primary:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    .hero .btn--ghost {
        border: 1px solid #cbd5e1;
        color: #334155;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(8px);
    }

    .hero .btn--ghost:hover {
        background: rgba(255, 255, 255, 0.9);
    }

    .hero .visual {
        position: relative;
        height: var(--hero-h);
    }

    .hero .layer {
        position: absolute;
        height: var(--hero-h);
        width: 88%;
        border-radius: var(--hero-radius);
        transition: transform var(--dur) var(--ease), filter var(--dur) var(--ease);
        filter: blur(0.2px);
    }

    .hero .layer--rose {
        left: -0.5rem;
        top: 0.75rem;
        background: rgba(244, 114, 182, 0.55);
    }

    .hero .layer--sky {
        left: 0.75rem;
        top: -0.5rem;
        width: 92%;
        background: rgba(56, 189, 248, 0.55);
    }

    .hero .card {
        position: relative;
        height: var(--hero-h);
        border-radius: var(--hero-radius);
        overflow: hidden;
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(8px);
        box-shadow: var(--ring), var(--shadow);
        transform-style: preserve-3d;
        transition: transform var(--dur) var(--ease),
            box-shadow var(--dur) var(--ease);
    }

    .hero .card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hero .chips {
        position: absolute;
        left: 0;
        right: 0;
        bottom: -2.5rem;
        display: flex;
        justify-content: center;
        gap: 0.65rem;
        z-index: 10;
    }

    .hero .chip {
        height: 2.5rem;
        width: 2.5rem;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: #fff;
        box-shadow: var(--ring), var(--shadow-soft);
        transition: transform var(--dur) var(--ease),
            box-shadow var(--dur) var(--ease);
    }

    .hero .chip--lg {
        height: 4rem;
        width: 4rem;
        transform: translateY(-12px) scale(1.03) !important;
    }

    .hero .icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #0f172a;
    }

    .hero .dot {
        display: inline-block;
        height: 0.75rem;
        width: 0.75rem;
        border-radius: 999px;
    }

    .hero .toast {
        position: absolute;
        right: 1rem;
        top: 1rem;
    }

    .hero .toast__inner {
        display: flex;
        gap: 0.65rem;
        align-items: center;
        color: #fff;
        background: rgba(15, 23, 42, 0.92);
        padding: 0.65rem 0.8rem;
        border-radius: 0.9rem;
        box-shadow: var(--ring), var(--shadow-soft);
    }

    .hero .toast .icon {
        color: #fff;
        height: 1.1rem;
        width: 1.1rem;
    }

    .hero .toast__title {
        font-weight: 700;
        font-size: 0.9rem;
        margin: 0;
    }

    .hero .toast__btn {
        margin-top: 0.25rem;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border: 0;
        border-radius: 0.4rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .hero .toast__btn:hover {
        background: rgba(255, 255, 255, 0.25);
        cursor: pointer;
    }

    /* HOVER PARALLAX + LITTLE WIGGLE (CSS-only) */
    .hero .card {
        transform: rotate3d(1, -1, 0, 6deg);
        animation: wiggle 600ms var(--ease) 1;
    }

    .hero .layer--rose {
        transform: translate(-18px, 16px) rotate(-2deg);
    }

    .hero .layer--sky {
        transform: translate(16px, -14px) rotate(2deg);
    }

    .hero .chip {
        transform: translateY(-3px) scale(1.03);
    }


    @keyframes wiggle {
        0% {
            transform: rotate3d(1, -1, 0, 0deg);
        }

        40% {
            transform: rotate3d(1, -1, 0, 6deg);
        }

        70% {
            transform: rotate3d(1, -1, 0, 3.5deg);
        }

        100% {
            transform: rotate3d(1, -1, 0, 6deg);
        }
    }

    /* Motion safety */
    @media (prefers-reduced-motion: reduce) {

        .hero .card,
        .hero .layer,
        .hero .chip {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>
