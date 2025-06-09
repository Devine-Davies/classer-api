<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - The essential accessory for your action camera & drones</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased">
    @include('partials.shared.naviagtion')

    @if(session('openApp'))
        <script>
            setTimeout(() => {
                // window.location.href = @json($openApp);
            }, 5000);
        </script>
    @endif

    @if($subscription)
        <div class="bg-green-100 p-3 rounded">
            <div class="py-2 px-4 m-auto max-w-7xl">
                <p>
                    Congratulations! Your subscription of <strong>{{ $subscription['name'] }}</strong> has been successfully activated. We will redirect you to the app in a few seconds.
                    If you are not redirected, please <a href="{{$openApp}}" class="text-blue-600 hover:underline">click here</a> to go to the app.
                </p>
            </div>
        </div>
    @endif

    <section class="py-8 px-4 m-auto max-w-7xl">
        <p class="block antialiased font-sans leading-relaxed text-blue-gray-900 mb-4 font-bold text-lg">
            Pricing Plans
        </p>
        <h1 class="block antialiased tracking-normal font-sans font-semibold text-blue-gray-900 mb-4 !leading-snug lg:!text-4xl !text-2xl max-w-2xl">
            Invest in a plan that's as ambitious as your corporate goals.
        </h1>
        <p class="block antialiased font-sans text-xl leading-relaxed text-inherit mb-10 font-normal !text-gray-500 max-w-xl">
            Compare the benefits and features of each plan below to find the ideal match for your business's budget and ambitions.
        </p>

        <div class="grid gap-x-10 gap-y-8 md:grid-cols-2 lg:grid-cols-3 max-w-5xl">
        @foreach($subscriptions as $plan)
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md">
            <div class="relative bg-clip-border !mt-4 mx-4 rounded-xl overflow-hidden bg-transparent text-gray-700 shadow-none !m-0 p-6">
                <h6 class="block antialiased tracking-normal font-sans text-base leading-relaxed text-blue-gray-900 capitalize font-bold mb-1">
                    {{ $plan['name'] }}
                </h6>
                <p class="block antialiased font-sans text-sm leading-normal text-inherit font-normal !text-gray-500">
                    {{ $plan['description'] }}
                </p>
                <h3 class="antialiased tracking-normal font-sans font-semibold leading-snug text-blue-gray-900 !mt-4 flex gap-1 !text-4xl">
                    ${{ $plan['price'] }}
                <span class="block antialiased font-sans leading-relaxed text-blue-gray-900 -translate-y-0.5 self-end opacity-70 text-lg font-bold">
                    /{{ $plan['interval'] }}
                </span>
                </h3>
            </div>

            <div class="p-6 pt-0">
                <ul class="flex flex-col gap-3 mb-6">
                @foreach($plan['features'] as $feature)
                    <li class="flex items-center gap-3 text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-blue-gray-900">
                        <path fill-rule="evenodd" d="M2.25 12a9.75 9.75 0 1119.5 0 9.75 9.75 0 01-19.5 0zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53-1.624-1.624a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                    <p class="block antialiased font-sans text-sm leading-normal font-normal text-inherit">{!! $feature !!}</p>
                    </li>
                @endforeach
                </ul>

                <form method="POST" action="{{ route('subscriptions.select') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan['code'] }}">
                    <button
                        type="submit"
                        class="align-middle select-none font-sans font-bold text-center uppercase transition-all disabled:opacity-50 disabled:shadow-none disabled:pointer-events-none text-xs py-3 px-6 rounded-lg bg-gradient-to-tr from-gray-900 to-gray-800 text-white shadow-md shadow-gray-900/10 hover:shadow-lg hover:shadow-gray-900/20 active:opacity-[0.85] block w-full"
                    >
                        {{ $plan['cta'] ?? 'Get Started' }}
                    </button>
                </form>
            </div>
            </div>
        @endforeach
        </div>

        <p class="block antialiased font-sans text-sm leading-normal text-inherit mt-10 font-normal !text-gray-500">
        You have Free Unlimited Updates and Premium Support on each package. You also have 30 days to request a refund.
        </p>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')
</body>

</html>
