@php
    $colspan = $colspan ?? 1;
    $title = $title ?? 'Nothing to show';
    $message = $message ?? 'Try adjusting your search or filters.';
@endphp

<td colspan="{{ $colspan }}" class="px-4 py-14">
    <div class="mx-auto flex max-w-sm flex-col items-center text-center">
        <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full border border-[#e2eaf0] bg-[#f8fafc] text-[#94a3b8]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
        </div>

        <p class="text-sm font-semibold text-[#334155]">{{ $title }}</p>
        <p class="mt-1 text-[0.82rem] leading-5 text-[#64748b]">{{ $message }}</p>
    </div>
</td>
