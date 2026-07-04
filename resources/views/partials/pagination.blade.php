@props([
    'currentPage',
    'lastPage',
    'baseQuery' => [],
    'label' => 'Pagination',
    'windowSize' => 5,
])

@php
    $currentPage = (int) $currentPage;
    $lastPage = (int) $lastPage;
    $windowSize = (int) $windowSize;

    $start = max(1, $currentPage - (int) floor($windowSize / 2));
    $end = min($lastPage, $start + $windowSize - 1);
    $start = max(1, $end - $windowSize + 1);

    $pageUrl = fn (int $page) =>
        '?' . http_build_query(array_merge($baseQuery, ['page' => $page]));

    $baseButtonClass = 'inline-flex h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm font-medium transition';

    $normalButtonClass = 'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50';

    $activeButtonClass = 'border-blue-600 bg-blue-600 text-white shadow-sm hover:border-blue-600 hover:bg-blue-600';

    $disabledButtonClass = 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400';
@endphp

@if ($lastPage > 1)
    <nav
        class="flex items-center justify-center py-4"
        aria-label="{{ $label }}"
    >
        <div class="flex items-center gap-2">

            {{-- Previous --}}
            @if ($currentPage > 1)
                <a
                    href="{{ $pageUrl($currentPage - 1) }}"
                    class="{{ $baseButtonClass }} {{ $normalButtonClass }}"
                >
                    Previous
                </a>
            @else
                <span
                    class="{{ $baseButtonClass }} {{ $disabledButtonClass }}"
                    aria-disabled="true"
                >
                    Previous
                </span>
            @endif

            {{-- First page --}}
            @if ($start > 1)
                <a
                    href="{{ $pageUrl(1) }}"
                    class="{{ $baseButtonClass }} {{ $currentPage === 1 ? $activeButtonClass : $normalButtonClass }}"
                >
                    1
                </a>

                @if ($start > 2)
                    <span class="px-2 text-sm text-gray-400">
                        ...
                    </span>
                @endif
            @endif

            {{-- Window pages --}}
            @for ($page = $start; $page <= $end; $page++)
                <a
                    href="{{ $pageUrl($page) }}"
                    class="{{ $baseButtonClass }} {{ $page === $currentPage ? $activeButtonClass : $normalButtonClass }}"
                    @if ($page === $currentPage)
                        aria-current="page"
                    @endif
                >
                    {{ $page }}
                </a>
            @endfor

            {{-- Last page --}}
            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <span class="px-2 text-sm text-gray-400">
                        ...
                    </span>
                @endif

                <a
                    href="{{ $pageUrl($lastPage) }}"
                    class="{{ $baseButtonClass }} {{ $currentPage === $lastPage ? $activeButtonClass : $normalButtonClass }}"
                >
                    {{ $lastPage }}
                </a>
            @endif

            {{-- Next --}}
            @if ($currentPage < $lastPage)
                <a
                    href="{{ $pageUrl($currentPage + 1) }}"
                    class="{{ $baseButtonClass }} {{ $normalButtonClass }}"
                >
                    Next
                </a>
            @else
                <span
                    class="{{ $baseButtonClass }} {{ $disabledButtonClass }}"
                    aria-disabled="true"
                >
                    Next
                </span>
            @endif

        </div>
    </nav>
@endif
