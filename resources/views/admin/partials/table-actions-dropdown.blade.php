@php
    $items = is_array($items ?? null) ? array_values($items) : [];
    $buttonLabel = trim((string) ($buttonLabel ?? 'Actions'));

    $buttonClass = $buttonClass
        ?? 'relative flex items-center whitespace-nowrap justify-center gap-2 py-1.5 rounded-lg shadow-sm bg-white hover:bg-gray-50 text-gray-800 border border-gray-200 hover:border-gray-200 px-3 text-xs font-semibold';

    $panelClass = $panelClass
        ?? 'absolute right-0 min-w-44 rounded-lg shadow-sm mt-2 z-10 origin-top-right bg-white p-1.5 outline-none border border-gray-200';

    $itemColorClasses = [
        'slate' => 'text-gray-800 hover:bg-gray-50 focus-visible:bg-gray-50',
        'emerald' => 'text-emerald-700 hover:bg-emerald-50 focus-visible:bg-emerald-50',
        'amber' => 'text-amber-700 hover:bg-amber-50 focus-visible:bg-amber-50',
        'rose' => 'text-rose-600 hover:bg-rose-50 focus-visible:bg-rose-50',
    ];
@endphp

<div class="flex justify-end">
    <div
        x-data="{
            open: false,
            toggle() {
                if (this.open) {
                    return this.close();
                }

                this.$refs.button.focus();
                this.open = true;
            },
            close(focusAfter) {
                if (!this.open) {
                    return;
                }

                this.open = false;

                if (focusAfter) {
                    focusAfter.focus();
                }
            }
        }"
        x-on:keydown.escape.prevent.stop="close($refs.button)"
        x-on:focusin.window="!$refs.panel.contains($event.target) && close()"
        x-id="['table-actions-dropdown']"
        class="relative"
    >
        <button
            x-ref="button"
            x-on:click="toggle()"
            :aria-expanded="open"
            :aria-controls="$id('table-actions-dropdown')"
            type="button"
            class="{{ $buttonClass }}"
        >
            <span>{{ $buttonLabel }}</span>

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            x-ref="panel"
            x-show="open"
            x-transition.origin.top.right
            x-on:click.outside="close($refs.button)"
            :id="$id('table-actions-dropdown')"
            x-cloak
            class="{{ $panelClass }}"
        >
            @foreach ($items as $item)
                @php
                    $label = trim((string) ($item['label'] ?? 'Action'));
                    $url = trim((string) ($item['url'] ?? ''));
                    $method = strtoupper(trim((string) ($item['method'] ?? 'GET')));
                    $color = strtolower(trim((string) ($item['color'] ?? 'slate')));
                    $confirm = trim((string) ($item['confirm'] ?? ''));
                    $disabled = (bool) ($item['disabled'] ?? false);
                    $fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
                    $itemClass = $itemColorClasses[$color] ?? $itemColorClasses['slate'];
                @endphp

                @if ($method === 'GET')
                    <a
                        href="{{ $url !== '' ? $url : '#' }}"
                        x-on:click="close()"
                        @if ($disabled) aria-disabled="true" @endif
                        class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left {{ $itemClass }} {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}"
                    >
                        {{ $label }}
                    </a>
                @else
                    <form method="POST" action="{{ $url }}" @if ($confirm !== '') data-confirm="{{ $confirm }}" onsubmit="return confirm(this.dataset.confirm);" @endif>
                        @csrf
                        @if (! in_array($method, ['GET', 'POST'], true))
                            @method($method)
                        @endif

                        @foreach ($fields as $fieldName => $fieldValue)
                            <input type="hidden" name="{{ $fieldName }}" value="{{ is_scalar($fieldValue) ? (string) $fieldValue : '' }}">
                        @endforeach

                        <button
                            type="submit"
                            x-on:click="close()"
                            @if ($disabled) disabled @endif
                            class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors text-left {{ $itemClass }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                        >
                            {{ $label }}
                        </button>
                    </form>
                @endif
            @endforeach
        </div>
    </div>
</div>
