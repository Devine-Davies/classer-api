@php
    $steps = [
        'details' => ['label' => 'Details', 'number' => '01'],
        'delivery' => ['label' => 'Delivery', 'number' => '02'],
        'payment' => ['label' => 'Payment', 'number' => '03'],
    ];
    $currentIndex = array_search($step, array_keys($steps), true);
@endphp

<div class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        @foreach ($steps as $key => $stepItem)
            @php
                $index = array_search($key, array_keys($steps), true);
                $isCurrent = $key === $step;
                $isComplete = $currentIndex !== false && $index < $currentIndex;
            @endphp
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border text-sm font-semibold {{ $isCurrent ? 'border-brand-color bg-brand-color text-white' : ($isComplete ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-500') }}">
                    {{ $stepItem['number'] }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] {{ $isCurrent ? 'text-brand-color' : ($isComplete ? 'text-emerald-700' : 'text-slate-400') }}">
                        {{ $isComplete ? 'Complete' : ($isCurrent ? 'Current step' : 'Upcoming') }}
                    </p>
                    <p class="truncate text-sm font-medium text-slate-900">{{ $stepItem['label'] }}</p>
                </div>
                @if (!$loop->last)
                    <div class="mx-2 hidden h-px flex-1 bg-slate-200 md:block"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>