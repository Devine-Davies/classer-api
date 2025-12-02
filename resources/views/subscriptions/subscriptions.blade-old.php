@php
    $listItem = function ($label) {
        return <<<HTML
            <li class="flex items-start gap-x-2">
                <svg class="star-icon-color w-6 h-6 " xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="28"><path d="M10 16.207l-6.173 3.246 1.179-6.874L.01 7.71l6.902-1.003L10 .453l3.087 6.254 6.902 1.003-4.995 4.869 1.18 6.874z"></path></svg>
                <span>$label</span>
            </li>
        HTML;
    };
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Subscriptions</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')

    @if (session('openApp'))
        <script>
            setTimeout(() => {
                window.location.href = @json($openApp);
            }, 5000);
        </script>
    @endif

    @if ($user)
        <div class="bg-green-100 p-3 rounded">
            <div class="py-2 px-4 m-auto max-w-7xl">
                <p>Welcome, <strong>{{ $user->name }}</strong>!</p>

                @if ($subscription)
                    <p>
                        Congratulations! Your subscription of
                        <strong>{{ $subscription['name'] }}</strong> has been
                        successfully activated. We will redirect you to the app in a
                        few seconds. If you are not redirected, please
                        <a href="{{ $openApp }}" class="text-blue-600 hover:underline">click here</a>
                        to go to the app.
                    </p>
                @endif
            </div>
        </div>
    @endif

    <section class="py-8 px-4 m-auto max-w-7xl">
        <p class="block antialiased font-sans leading-relaxed text-blue-gray-900 mb-4 font-bold text-lg">
            Pricing Plans
        </p>
        <h1
            class="block antialiased tracking-normal font-sans font-semibold text-blue-gray-900 mb-4 !leading-snug lg:!text-4xl !text-2xl max-w-2xl">
            Invest in a plan that's as ambitious as your corporate goals.
        </h1>
        <p
            class="block antialiased font-sans text-xl leading-relaxed text-inherit mb-10 font-normal !text-gray-500 max-w-xl">
            Compare the benefits and features of each plan below to find the
            ideal match for your business's budget and ambitions.
        </p>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 mt-4">
            @foreach ($subscriptions as $sub)
                <div
                    class="{{ $sub['popular'] ? 'border-orange-400' : '' }} overflow-hidden relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md border border-gray-200 rounded-md">
                    <div class="{{ $sub['popular'] ? 'bg-badge' : 'bg-gray-100' }} p-4 py-6 w-full text-center">
                        <h2 class="text-2xl tracking-tight">
                            {{ $sub['name'] }}
                        </h2>
                        <p class="max-w-sm text-sm m-auto">{{ $sub['description'] }}</p>
                    </div>

                    <div class="px-6 mt-4">
                        <div
                            class="relative bg-clip-border !mt-4 mx-4 rounded-xl overflow-hidden bg-transparent text-gray-700 shadow-none !m-0">
                            <p class="flex items-baseline gap-x-0 mb-4">
                                <span class="text-4xl font-bold tracking-tight text-gray-900">$10</span><span
                                    class="flex flex-row gap-1 items-baseline"><span
                                        class="text-gray-600 text-sm font-medium">/month </span><span
                                        class="text-gray-500 text-xs italic font-medium">
                                        (billed annually)
                                    </span></span>
                            </p>

                            @if ($user)
                                @if (!$user['subscription'])
                                    <form method="POST" action="{{ route('subscriptions.redirect') }}">
                                        @csrf
                                        <input type="hidden" name="code" value="{{ $sub['code'] }}" />
                                        <button type="submit"
                                            class="btn {{ $sub['popular'] ? '' : 'btn-outline' }} btn--lg w-full btn--xl">
                                            Get Plan
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ $openApp }}"
                                        class="btn block text-center {{ $sub['popular'] ? '' : 'btn-outline' }} btn--lg w-full btn--xl">
                                        Open App
                                    </a>
                                @endif
                            @else
                                <a href="?modal=download" data-modal-open
                                    class="btn block text-center {{ $sub['popular'] ? '' : 'btn-outline' }} btn--lg w-full btn--xl">
                                    Get Started
                                </a>
                            @endif
                        </div>

                        <ul class="my-4 space-y-2 text-sm leading-6 text-gray-600 xl:mt-2">
                            <li class="flex flex-col gap-2">
                                <div class="py-5">
                                    @foreach ($sub['featuresSets'] as $featureSet)
                                        <h4 class="font-semibold text-gray-900 mt-3 mb-1">
                                            {{ $featureSet['title'] }}
                                        </h4>
                                        <ul class="space-y-1">
                                            @foreach ($featureSet['features'] as $features)
                                                {!! $listItem($features) !!}
                                            @endforeach
                                        </ul>
                                    @endforeach
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="block antialiased font-sans text-sm leading-normal text-inherit mt-10 font-normal !text-gray-500">
            You have Free Unlimited Updates and Premium Support on each
            package. You also have 30 days to request a refund.
        </p>
    </section>

    @include('partials.shared.footer') @include('partials.shared.modals')
</body>

</html>
