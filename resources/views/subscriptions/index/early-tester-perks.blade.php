{{-- 
  Early Tester Perks Section
  Expects (all optional):
  - $title (string) : Section headline
  - $subtitle (string) : Supporting text below title
  - $perks (array) : Each item is ['heading' => '', 'sub' => '']
  - $ctaText (string) : Button text
  - $ctaHref (string|url) : Button link
--}}

<section class="mx-auto max-w-7xl px-6 py-14">
    <div class="text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
            {{ $title ?? "What you'll get as one of our early testers:" }}
        </h2>

        @if(!empty($subtitle))
            <p class="mt-3 text-slate-600">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2">
        @foreach($perks ?? [] as $perk)
            <div class="flex items-start gap-4 p-5">
                {{-- Inline checkmark icon --}}
                <svg class="mt-1 h-6 w-6 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>

                <div>
                    <p class="font-semibold text-2xl text-slate-900">{{ $perk['heading'] ?? '' }}</p>
                    @if(!empty($perk['sub']))
                        <p class="mt-1 leading-6 text-slate-600">{{ $perk['sub'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if(!empty($ctaText) && !empty($ctaHref))
        <div class="mt-10 flex justify-center">
            <a href="{{ $ctaHref }}"
               class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 font-semibold text-white hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                {{ $ctaText }}
            </a>
        </div>
    @endif
</section>