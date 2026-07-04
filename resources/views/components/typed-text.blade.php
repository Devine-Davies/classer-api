{{-- resources/views/components/typed-text.blade.php --}}

@props([
    'strings' => [],
    'typeSpeed' => 40,
    'backSpeed' => 25,
    'loop' => true,
    'delay' => 1200,
])

<span
    {{ $attributes->merge(['class' => 'inline-block min-h-[1em]']) }}
    x-data="{
        typed: null,

        init() {
            this.$nextTick(() => {
                setTimeout(() => {
                    if (!window.Typed) {
                        console.error('Typed.js is not loaded on window');
                        return;
                    }

                    this.typed = new window.Typed(this.$el, {
                        strings: @js($strings),
                        typeSpeed: {{ $typeSpeed }},
                        backSpeed: {{ $backSpeed }},
                        backDelay: 1200,
                        startDelay: 300,
                        loop: @js($loop),
                    });
                }, {{ $delay }});
            });
        },

        destroy() {
            this.typed?.destroy();
        }
    }"
></span>