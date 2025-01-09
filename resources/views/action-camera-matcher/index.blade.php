@php
    $optionInputCls =
        'appearance-none h-6 w-6 border border-gray-300 rounded-full bg-grey-50 checked:bg-blue-600 checked:border-transparent focus:outline-none focus:ring-0 focus:ring-blue-600';        

    $formData = $questionnaire['questions'];
    $logosImgPaths = [
        'akaso' => asset('/assets/images/welcome/logos/akaso.png'),
        'sjcam' => asset('/assets/images/welcome/logos/sjcam.png'),
        'dji' => asset('/assets/images/welcome/logos/dji.png'),
        'go-pro' => asset('/assets/images/welcome/logos/go-pro.png'),
        'insta360' => asset('/assets/images/welcome/logos/insta360.png'),
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
    @vite('resources/views/action-camera-matcher/index.css')
    @vite('resources/views/action-camera-matcher/index.js')
</head>

<body class="antialiased">
    @include('partials.shared.naviagtion')

    <section class="bg-white">
        <div class="relative px-3 md:pt-12 mx-auto lg:py-32 md:px-8 xl:px-20 md:max-w-full">
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

                    <div class="hidden grid-cols-3 md:grid-cols-5 xl:grid">
                        @foreach ($logosImgPaths as $logoName => $logoImgPath)
                            <div class="h-16 flex align-center justify-center">
                                <img class="m-auto w-6/12" src="{{ $logoImgPath }}"
                                    alt="{{ ucfirst($logoName) }} logo" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center h-full lg:w-2/3 xl:w-1/2 lg:absolute lg:justify-start lg:bottom-0 lg:right-0 lg:items-end">
                <img src="{{ asset('/assets/images/action-camera-matcher/cameras@2x.png') }}"
                    class="object-cover -mt-20 md:-mt-28 object-top w-full h-64 max-w-xl lg:ml-64 xl:ml-8 lg:-mb-24 lg:h-auto"
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
        class="hidden max-h-full py-4 top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center backdrop-blur-md fixed h-auto">
        <div class="m-auto w-1/1 max-w-5xl relative h-full overflow-auto flex flex-col rounded-lg">
            <div class="bg-white rounded-lg shadow p-8">
                <button data-modal-close
                    class="fixed top-5 right-10 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                    <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
                    <span class="sr-only">Close modal</span>
                </button>

                <form id="form">
                    @csrf

                    @for ($i = 0; $i < count($formData); $i++)
                        @php
                            $isFirstQuestion = $i === 0;
                            $isLastQuestion = $i === count($formData) - 1;
                        @endphp

                        <div class="m-auto max-w-lg hidden" id="form-question-block-{{ $i }}"
                            data-question-block-idx="{{ $i }}">
                            <h1 class="text-xl lg:text-4xl font-bold text-center text-brand-color mb-6">
                                {{ $formData[$i]['title'] }}
                            </h1>

                            <div class="flex flex-col m-auto my-12 scale-110 relative -right-5">
                                @for ($j = 0; $j < count($formData[$i]['options']); $j++)
                                    @php
                                        $option = $formData[$i]['options'][$j];
                                        $isMultipleChoice = array_key_exists('multipleChoice', $formData[$i]) &&
                                            $formData[$i]['multipleChoice'];
                                        $isLastOption = $j === count($formData[$i]['options']) - 1;
                                    @endphp

                                    @if ($isMultipleChoice)
                                        // @TODO: This is not implemented
                                        <div class="flex items center">
                                            <input type="checkbox" id="{{ $i }}-{{ $j }}"
                                                name="options-{{ $i }}[]" value="{{ $j }}"
                                                class="{{ $optionInputCls }}" />

                                            <label class="cursor-pointer text-md hover:underline px-5 py-2"
                                                for="{{ $i }}-{{ $j }}">{{ $option }}</label>
                                        </div>
                                    @endif

                                    @if (!$isMultipleChoice)
                                        <div class="flex items-center">
                                            <input type="radio" id="{{ $i }}-{{ $j }}"
                                                name="options-{{ $i }}" value="{{ $j }}"
                                                class="{{ $optionInputCls }}" />

                                            <label class="cursor-pointer text-md hover:underline px-5 py-2"
                                                for="{{ $i }}-{{ $j }}">{{ $option }}</label>
                                        </div>
                                    @endif
                                @endfor
                            </div>

                            <div class="flex items-center justify-between mt-8 sticky bottom-0">
                                <p class="text-gray-500">
                                    Question <span class="font-semibold">{{ $i + 1 }}</span> of
                                    {{ count($formData) }}
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

                <div data-results class="acm-results-pane flex flex-col h-full hidden" >
                    <div class="relative w-full mb-6 text-center">
                        <a data-reset class="btn-simple btn--sm absolute left-0 top-1/2 transform -translate-y-1/2">
                            @icon(chevron-left)
                        </a>
                        <h1 class="text-xl lg:text-4xl font-bold text-brand-color">
                            We recommend you
                        </h1>
                    </div>
                    <ul></ul>
                    <div class="flex">
                        <a data-view-all-results class="m-auto my-4 self-end inline-flex align-middle justify-center text-brand-color cursor-pointer w-auto ml-0 hover:underline">
                            @icon(chevron-down)
                            View all results
                        </a>
                    </div>
                </div>
            </div>

            <div data-classer-billboard class="hidden -mt-8">
                <section class="bg-gray-100 overflow-hidden">
                    <div class="container flex flex-col px-10 mx-auto space-y-6 lg:flex-row lg:items-center">
                        <div class="w-full lg:w-1/2">
                            <div class="lg:max-w-lg">
                                <h1 class="text-xl md:text-3xl font-bold text-brand-color mb-3 tracking-wide">
                                    Make the most of your action camera
                                </h1>

                                @php
                                    $featureItems = [
                                        'Optimized for Action Cameras',
                                        'Compress and save space',
                                        'Search, filter and organize your videos',
                                    ];
                                @endphp

                                <div class="mt-8 space-y-2">
                                    @foreach ($featureItems as $featureItem)
                                        <p class="flex items -center -mx-2 text-gray-700">
                                            @icon(tick)
                                            <span class="mx-2">{{ $featureItem }}</span>
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                            <a type="button" class="btn mt-8" href="/auth/register">
                                Join For Free
                            </a>
                        </div>

                        <div class="flex items-center justify-center w-full">
                            <img class="object-contain w-full h-full mx-auto rounded-md lg:max-w-2xl scale-x-125 translate-x-44 translate-y-8"
                                src="{{ asset('/assets/images/welcome/hero/image-1.jpg') }}" alt="glasses photo">
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </article>
</body>

<!-- js template -->
<script type="text/template" id="template-acm-results-title">
    <div class="flex-1">
        <p class="text-md font-bold text-gray-700 truncate">
            ${key}
        </p>
    </div>
</script>

<script type="text/template" id="template-acm-results-toggle-benefits-button">
    <button data-toggle-open="${index}" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">
        @icon(chevron-down)
    </button>
</script>

<script type="text/template" id="template-acm-results-benefits-item">
    <li class="flex items-center">
        <span>@icon(tick)</span>
        <span>${benefit}</span>
    </li>
</script>

<script type="text/template" id="template-acm-results-item">
    <li class="recommendation-item py-4 ${recommendationKey}">
        <div class="flex space-between items-center justify-center relative">
            ${rankingImage}

            <img class="object-contain w-full max-w-[175px] h-auto mx-auto"
                src="${thumbnail}" alt="glasses photo">

            <div class="flex flex-col justify-center flex-auto">
                <h3 class="text-xl font-bold text-brand-color pl-2 mb-1">
                    ${title}
                </h3>
                ${benefits}
            </div>

            <div class="flex flex-col items-center justify-center gap-1">
                <button class="btn">Buy Camera</button>
                <p class="text-gray-500 text-xs">${recommendation}</p>
            </div>
        </div>
    </li>
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        grecaptcha.ready(function() {
            grecaptcha.execute('6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq', {
                action: 'submit'
            }).then(function(token) {
                document.querySelector('#form').insertAdjacentHTML('beforeend',
                    '<div class="hidden" ><input id="grc-token" type="hidden" name="grc" value="' +
                    token + '"></div>');
            });
        });
    });
</script>

</html>
