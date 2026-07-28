@props([
    'items' => null,
    'homeLabel' => 'Admin',
    'homeUrl' => url('/admin'),
    'basePath' => 'admin',
])

@php
    use Illuminate\Support\Str;

    if ($items === null) {
        $path = trim(request()->path(), '/');
        $currentRouteName = (string) optional(request()->route())->getName();

        if ($currentRouteName === 'admin.stats.details') {
            $domain = (string) request()->route('domain', request()->query('domain', 'users'));

            $items = [
                [
                    'label' => $homeLabel,
                    'url' => $homeUrl,
                ],
                [
                    'label' => 'Stats',
                    'url' => route('admin.stats'),
                ],
                [
                    'label' => Str::headline(str_replace(['-', '_'], ' ', $domain)),
                    'url' => route('admin.stats.details', ['domain' => $domain]),
                ],
            ];
        }

        if ($items === null) {
            $segments = collect(explode('/', $path))
                ->filter()
                ->values();

            $baseSegments = collect(explode('/', trim($basePath, '/')))
                ->filter()
                ->values();

            $segments = $segments
                ->slice($baseSegments->count())
                ->values();

            $builtItems = [
                [
                    'label' => $homeLabel,
                    'url' => $homeUrl,
                ],
            ];

            $currentPath = trim($basePath, '/');

            foreach ($segments as $segment) {
                $currentPath .= '/' . $segment;

                $isUuid = Str::isUuid($segment);

                $builtItems[] = [
                    'label' => $isUuid
                        ? 'Details'
                        : Str::headline(str_replace(['-', '_'], ' ', $segment)),
                    'url' => url($currentPath),
                ];
            }

            $items = $builtItems;
        }
    }
@endphp

@if (! empty($items))
    <nav {{ $attributes->merge([]) }} aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            @foreach ($items as $item)
                @php
                    $label = $item['label'] ?? '';
                    $url = $item['url'] ?? null;
                    $isLast = $loop->last;
                @endphp

                <li class="flex items-center gap-2">
                    @if (! $isLast && $url)
                        <a
                            href="{{ $url }}"
                            class="font-medium text-slate-500 transition hover:text-slate-900"
                        >
                            {{ $label }}
                        </a>
                    @else
                        <span
                            class="{{ $isLast ? 'font-semibold text-slate-900' : 'font-medium text-slate-500' }}"
                            @if ($isLast) aria-current="page" @endif
                        >
                            {{ $label }}
                        </span>
                    @endif

                    @if (! $isLast)
                        <span class="text-slate-300" aria-hidden="true">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif