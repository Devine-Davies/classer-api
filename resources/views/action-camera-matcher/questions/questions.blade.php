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
    @vite('resources/views/action-camera-matcher/questions/questions.css')
    @vite('resources/views/action-camera-matcher/questions/questions.js')
</head>

<body class="antialiased">
    @include('partials.shared.navigation')

    <section class="bg-white">
        <div class="relative px-3 md:pt-3 mx-auto lg:py-8 md:px-8 xl:px-20 md:max-w-full">
            <form id="form">
                @csrf

                @for ($i = 0; $i < count($formData); $i++)
                    @php
                    $isFirstQuestion=$i===0;
                    $isLastQuestion=$i===count($formData) - 1;
                    @endphp

                    <div class="m-auto max-w-lg hidden" id="form-question-block-{{ $i }}"
                    data-question-block-idx="{{ $i }}">
                    <h1 class="text-xl lg:text-4xl font-bold text-center text-brand-color mb-6">
                        {{ $formData[$i]['title'] }}
                    </h1>

                    <div class="flex flex-col m-auto my-12 scale-110 relative -right-5">
                        @for ($j = 0; $j < count($formData[$i]['options']); $j++)
                            @php
                            $option=$formData[$i]['options'][$j];
                            $isMultipleChoice=array_key_exists('multipleChoice', $formData[$i]) &&
                            $formData[$i]['multipleChoice'];
                            $isLastOption=$j===count($formData[$i]['options']) - 1;
                            @endphp

                            @if ($isMultipleChoice)
                            // @TODO: This is not implemented
                            <div class="flex items center">
                            <input type="checkbox" id="{{ $i }}-{{ $j }}"
                                autocomplete="off"
                                name="options-{{ $i }}[]" value="{{ $j }}"
                                class="{{ $optionInputCls }}" />

                            <label class="cursor-pointer text-md hover:underline px-5 py-2"
                                for="{{ $i }}-{{ $j }}">{{ $option }}</label>
                    </div>
                    @endif

                    @if (!$isMultipleChoice)
                    <div class="flex items-center">
                        <input type="radio" id="{{ $i }}-{{ $j }}"
                            autocomplete="off"
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

        <div data-results class="acm-results-pane flex flex-col h-full hidden">
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
    </section>

    <div class="fixed bottom-0 w-full">
        @include('partials.shared.footer')
    </div>
    @include('partials.shared.modals')
</body>

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