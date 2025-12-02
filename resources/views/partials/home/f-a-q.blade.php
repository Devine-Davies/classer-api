<style>
    /* Optional polish on cards / focus / search chip */
    .faq-card {
        transition: transform .12s ease, box-shadow .12s ease;
    }

    .faq-card:focus-within {
        outline: 2px solid rgba(59, 130, 246, .6);
        /* blue-500/60 */
        outline-offset: 2px;
    }

    .faq-card:hover {
        transform: translateY(-1px);
    }

    .chip {
        font-size: .75rem;
        padding: .125rem .5rem;
        border-radius: 9999px;
        background: rgba(0, 0, 0, .06);
    }
</style>

{{-- Alpine.js (CDN) --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="faqComponent({ faqs: @js($faqs) })" x-init="init()" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-10">
    <header class="mb-6 text-center max-w-2xl m-auto">
        <h3 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color">Frequently asked questions
        </h3>
        <p class="mt-2">Helpful questions from the community. If you don't see one you're looking
            for, please reach at <a class="underline" href="mailto:contact@classermedia.com">contact@classermedia.com</a>.
        </p>
    </header>

    {{-- Search --}}
    <div class="mb-4 hidden">
        <label for="faq-search" class="sr-only">Search FAQs</label>
        <div class="relative">
            <input id="faq-search" type="search" x-model.debounce.200ms="q" placeholder="Search for a question"
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-12 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400">
                @icon(magnifyingGlass)
            </span>
            <button x-show="q.length" x-on:click="q=''; $nextTick(()=>document.getElementById('faq-search').focus())"
                type="button" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-2 hover:bg-white"
                aria-label="Clear search">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24"
                    fill="currentColor">
                    <path
                        d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.9 4.89a1 1 0 1 0 1.41 1.42L12 13.41l4.89 4.9a1 1 0 0 0 1.42-1.41L13.41 12l4.9-4.89a1 1 0 0 0-.01-1.4Z" />
                </svg>
            </button>
        </div>
        <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
            <div>
                <span x-text="filtered.length"></span>
                <span x-text="filtered.length === 1 ? 'result' : 'results'"></span>
                <template x-if="q.length">
                    <span>for “<span x-text="q"></span>”</span>
                </template>
            </div>
            <div class="space-x-2">
                <button type="button" class="chip hover:bg-white" x-on:click="expandAll()">Expand all</button>
                <button type="button" class="chip hover:bg-white" x-on:click="collapseAll()">Collapse all</button>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="space-y-4">
        <template x-for="(item, idx) in filtered" :key="idx">
            <section class="faq-card rounded-2xl bg-gray-50 shadow-sm ring-1 ring-black/5 overflow-hidden"
                :id="'faq-' + idx">
                <h3>
                    <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                        :aria-expanded="open.has(idx)" :aria-controls="'faq-panel-' + idx" x-on:click="toggle(idx)">
                        <div>
                            <span class="block text-base md:text-lg font-semibold" x-text="item.q"></span>
                            <span class="chip mt-1 inline-block" x-text="item.category"></span>
                        </div>
                        <!-- plus / close icons -->
                        <span class="shrink-0" x-show="!open.has(idx)">
                            @icon(menu)
                        </span>
                        <span class="shrink-0" x-show="open.has(idx)">
                            @icon(close)
                        </span>
                    </button>
                </h3>

                <div :id="'faq-panel-' + idx" x-show="open.has(idx)" x-transition.opacity
                    class="px-5 pb-5 text-sm text-gray-600">
                    <p x-text="item.a"></p>
                </div>
            </section>
        </template>

        <!-- Empty state -->
        <div x-show="!filtered.length" class="rounded-2xl border border-dashed p-8 text-center text-sm text-gray-500">
            No results. Try a different keyword.
        </div>
    </div>
</div>

<script>
    // Alpine "store-like" component with fuzzy-ish search (diacritics removed, case-insensitive).
    function faqComponent({
        faqs
    }) {
        return {
            faqs,
            q: '',
            open: new Set(), // track open item indices
            filtered: [],
            init() {
                this.filtered = this.faqs;
            },
            norm(t) {
                return (t || '')
                    .toString()
                    .toLowerCase()
                    .normalize('NFD') // split diacritics
                    .replace(/\p{Diacritic}/gu, '') // remove them
                    .trim();
            },
            matches(item) {
                if (!this.q) return true;
                const needle = this.norm(this.q);
                return this.norm(item.q).includes(needle) ||
                    this.norm(item.a).includes(needle) ||
                    this.norm(item.category || '').includes(needle);
            },
            toggle(idx) {
                this.open.has(idx) ? this.open.delete(idx) : this.open.add(idx);
            },
            expandAll() {
                this.filtered.forEach((_, i) => this.open.add(i));
            },
            collapseAll() {
                this.open.clear();
            }
        }
    }

    // Keep filtered list reactive to query changes
    document.addEventListener('alpine:init', () => {
        Alpine.magic('watchFilter', (el) => (cmp) => {
            cmp.$watch('q', () => {
                cmp.filtered = cmp.faqs.filter(item => cmp.matches(item));
                // reset open items when filtering (optional)
                cmp.open.clear();
            });
        });
    });
</script>

<script>
    // Apply the filter watcher once Alpine is ready
    document.addEventListener('alpine:initialized', () => {
        document.querySelectorAll('[x-data]').forEach(el => {
            const cmp = Alpine.$data(el);
            if (cmp && typeof cmp.$watch === 'function') {
                cmp.$watch('q', () => {
                    cmp.filtered = cmp.faqs.filter(item => cmp.matches(item));
                    cmp.open.clear();
                });
            }
        });
    });
</script>

{{-- @php
    $fAq = [
        [
            'question' => 'Is it for mobile?',
            'answer' => 'We are currently focusing on desktop, but with future plans to make it work for mobile too.',
        ],
        [
            'question' => 'Can I cut and trim my videos?',
            'answer' => 'Yes, Classer allows you to cut and trim your videos reducing file size so that they can be easily shared with other services.',
        ],
        [
            'question' => 'Is this a cloud service?',
            'answer' => 'Not yet but we are working on it ;).',
        ],
        [
            'question' => 'Does Classer use my directory from my folder file?',
            'answer' =>
                'Yes, Classer leverages the existing structure of your file folder, allowing you to get quickly onboarded and enabling faster access to what you\'re seeking.',
        ],
        [
            'question' => 'Does it work with all action cameras?',
            'answer' => 'Yes and all video file formats, including .mp4, .mov, .avi',
        ],
        [
            'question' => 'I would like to contact the team, how do I do it?',
            'answer' => 'Happy to chat! Please contact us at contact@classermedia.com',
        ],
        [
            'question' => 'How to turn on my GPS on my GoPro?',
            'answer' =>
                'From the main screen from GoPro, swipe down (HERO11/10/9 white, swipe left after swiping down) and tap [Preferences]. For HERO11 Black, scroll to [GPS] and turn GPS [On]. For HERO10/9 Black, scroll to [Regional], tap [GPS] and turn GPS [On].',
        ],
    ];
@endphp

<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">Common questions from users</h2>
<div id="faqs" class="grid text-left grid-cols-1 md:sp md:grid-cols-2 gap-x-12 m-auto max-w-sm md:max-w-6xl">
    @foreach ($fAq as $faq)
        <div @class([
            'mb-8' => !$loop->last,
        ])>
            <h3 class="flex items-center mb-4 mt-6 text-brand-color text-xl font-bold">
                {{ $faq['question'] }}
            </h3>
            <p class="md:max-w-xs">
                {{ $faq['answer'] }}
            </p>
        </div>
    @endforeach
</div>

<section class="mx-auto md:max-w-3xl pt-6 md:pt-12">
    @include('partials.home.available-for')
</section> --}}
