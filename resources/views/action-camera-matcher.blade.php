@php
    $optionInputCls =
        'appearance-none h-6 w-6 border border-gray-300 rounded-full bg-pink-50 checked:bg-blue-600 checked:border-transparent focus:outline-none focus:ring-0 focus:ring-blue-600';

    $formdata = $questionnaire['questions'];
    $logosImgPaths = [
        'akaso' => asset('/assets/images/welcome/logos/akaso.png'),
        'sjcam' => asset('/assets/images/welcome/logos/sjcam.png'),
        'dji' => asset('/assets/images/welcome/logos/dji.png'),
        'go-pro' => asset('/assets/images/welcome/logos/go-pro.png'),
        'insta360' => asset('/assets/images/welcome/logos/insta360.png')
    ];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Action Camera Matcher</title>
    <script>
        const questionnaire = @json($questionnaire);
    </script>

    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
    @vite('resources/js/action-camera-matcher.js')
</head>

<body class="antialiased">
    @include('partials.shared.naviagtion')

    <section class="bg-white dark:bg-gray-900 my-16">
        <div class="relative px-4 pt-16 mx-auto lg:py-32 md:px-8 xl:px-20 sm:max-w-xl md:max-w-full">
            <div class="max-w-5xl mx-auto">
                <div class="mb-16 lg:max-w-lg lg:mb-0">
                    <div class="max-w-xl mb-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-brand-color mb-6 tracking-wide">
                            Find the action camera that suits your needs
                        </h2>
                        <p class="text-base text-gray-700 md:text-lg">
                            Answer a few questions and we'll recommend the best action camera for you.
                        </p>
                    </div>

                    <div class="flex items-center">
                        <a aria-label="Download Classer" href="?modal=action-camera-matcher" data-modal-open
                            class="btn text-lg">
                            Start here
                        </a>
                    </div>

                    <div class="grid grid-cols-3 md:grid-cols-5">
                        @foreach ($logosImgPaths as $logoName => $logoImgPath)
                            <div class="h-16 flex align-center justify-center">
                                <img class="m-auto w-6/12" src="{{ $logoImgPath }}" alt="{{ ucfirst($logoName) }} logo" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center h-full lg:w-2/3 xl:w-1/2 lg:absolute lg:justify-start lg:bottom-0 lg:right-0 lg:items-end">
                <img src="{{ asset('/assets/images/action-camera-matcher/cameras@2x.png') }}"
                    class="object-cover object-top w-full h-64 max-w-xl -mb-16 lg:ml-64 xl:ml-8 lg:-mb-24 xl:-mb-28 lg:h-auto"
                    alt="" />
            </div>
        </div>
    </section>

    <section id="our-stories-section">
        <div class="mx-auto max-w-7xl px-6 py-6 md:py-12">
            @include('partials.home.our-stories')
            <div class="text-center mt-8 underline">
                <a href="/stories" class="text-center text-underline">View all</a>
            </div>
        </div>
    </section>

    <section id="join-our-community-section" class="bg-off-white">
        <div>
            @include('partials.home.join-our-community')
        </div>
    </section>

    @include('partials.shared.footer')
    @include('partials.shared.modals')

    <article tabindex="-1" data-modal="action-camera-matcher"
        class="hidden overflow-hidden max-h-full fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center backdrop-blur-md">
        <div class="p-4 m-auto w-1/1 max-w-5xl relative">
            <div class="p-12 px-20 bg-white rounded-lg shadow overflow-hidden">
                <button data-modal-close
                    class="absolute top-5 right-5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                    <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
                    <span class="sr-only">Close modal</span>
                </button>

                <form>
                    @csrf

                    @for ($i = 0; $i < count($formdata); $i++)
                        @php
                            $isFirstQuestion = $i === 0;
                            $isLastQuestion = $i === count($formdata) - 1;
                        @endphp

                        <div class="m-auto max-w-lg hidden" id="form-question-block-{{ $i }}"
                            data-question-block-idx="{{ $i }}">
                            <h1 class="text-xl lg:text-4xl font-bold text-center text-brand-color mb-6">
                                {{ $formdata[$i]['title'] }}
                            </h1>

                            <div class="flex flex-col m-auto my-12 scale-110 relative -right-5">
                                @for ($j = 0; $j < count($formdata[$i]['options']); $j++)
                                    @php
                                        $option = $formdata[$i]['options'][$j];
                                        $isLastOption = $j === count($formdata[$i]['options']) - 1;
                                    @endphp

                                    <div class="flex items-center">
                                        <input type="radio" id="{{ $i }}-{{ $j }}"
                                            name="options-{{ $i }}" value="{{ $j }}"
                                            class="{{ $optionInputCls }}" />

                                        <label class="cursor-pointer text-md hover:underline px-5 py-2"
                                            for="{{ $i }}-{{ $j }}">{{ $option }}</label>
                                    </div>
                                @endfor
                            </div>

                            <div class="flex items-center justify-between mt-8 sticky bottom-0">
                                <p class="text-gray-500">
                                    Question <span class="font-semibold">{{ $i + 1 }}</span> of
                                    {{ count($formdata) }}
                                </p>

                                <div>
                                    @if (!$isFirstQuestion)
                                        <button data-previous-question class="btn-simple font-semibold">
                                            Previous
                                        </button>
                                    @else
                                        <div></div>
                                    @endif

                                    <button class="btn self-end"
                                        {{ $isLastQuestion ? 'data-submit' : 'data-next-question' }}>
                                        {{ $isLastQuestion ? 'Submit' : 'Next' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endfor
                </form>

                <div class="acm-results-pane hidden" data-results>
                    <h1 class="text-4xl font-bold text-brand-color text-center mb-6">
                        Results
                    </h1>
                    <p class="mb-4">Based on your answers, we recommend the following action cameras Based on your
                        answers, we recommend the following action cameras:</p>
                    <ul></ul>
                    <div class="flex items-center justify-center pt-8">
                        <a class="btn" data-reset>
                            Back to start
                        </a>
                    </div>
                </div>
            </div>

            <div data-classer-billboard class="hidden bg-white rounded-lg shadow overflow-hidden mt-4">
                <section class="bg-white dark:bg-gray-900">
                    <div class="container flex flex-col px-10 mx-auto space-y-6 lg:flex-row lg:items-center">
                        <div class="w-full lg:w-1/2">
                            <div class="lg:max-w-lg">
                                <h1 class="text-3xl md:text-4xl font-bold text-brand-color mb-6 tracking-wide">
                                    Make the most of your action camera
                                </h1>

                                @php
                                    $featureItems = [
                                        'Optimized for Action Cameras',
                                        'Compress and save space',
                                        'Search, filter and organize your videos',
                                    ];
                                @endphp

                                <div class="mt-8 space-y-5">
                                    @foreach ($featureItems as $featureItem)
                                        <p class="flex items -center -mx-2 text-gray-700 dark:text-gray-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mx-2 text-blue-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="mx-2">{{ $featureItem }}</span>
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                            <a type="button" class="btn mt-8" href="/auth/register">
                                Join For Free
                            </a>
                        </div>

                        <div class="flex items-center justify-center w-full h-96 lg:w-1/2">
                            <img class="object-contain w-full h-full mx-auto rounded-md lg:max-w-2xl"
                                src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" alt="glasses photo">
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </article>
</body>

</html>
