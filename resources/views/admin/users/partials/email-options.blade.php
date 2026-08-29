@php
    $buttonClass = $buttonClass ?? 'admin-btn admin-btn-neutral';
    $menuAlign = $menuAlign ?? 'right-0';
@endphp

<div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button
        type="button"
        class="{{ $buttonClass }}"
        x-on:click="open = !open"
        x-bind:aria-expanded="open"
        aria-haspopup="menu"
    >
        <span>Email user</span>
        <span class="text-xs text-[#64748b]" aria-hidden="true">▾</span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.origin.top.left
        x-on:click.outside="open = false"
        class="absolute {{ $menuAlign }} z-40 mt-2 w-56 rounded-lg border border-[#dce6ef] bg-white p-1.5 shadow-lg"
        role="menu"
    >
        @foreach ($emailActions as $emailAction)
            <a
                href="{{ route('admin.email-broadcasts', ['template' => $emailAction['template'], 'emails' => $user->email ?? '']) }}"
                class="block rounded-md px-3 py-2 text-sm font-semibold text-[#334155] transition hover:bg-[#f1f5f9]"
                role="menuitem"
            >
                {{ $emailAction['label'] }}
            </a>
        @endforeach

        <div class="mt-1 border-t border-[#e2e8f0] pt-1">
            <a
                href="{{ route('admin.email-broadcasts', ['emails' => $user->email ?? '']) }}"
                class="block rounded-md px-3 py-2 text-sm font-semibold text-[#64748b] transition hover:bg-[#f1f5f9] hover:text-[#334155]"
                role="menuitem"
            >
                All email templates
            </a>
        </div>
    </div>
</div>