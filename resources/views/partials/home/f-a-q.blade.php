@php
    $fAq = [
        [
            'question' => 'Is it for mobile?',
            'answer' => 'We are currently focusing on desktop, but with future plans to make it work for mobile too.',
        ],
        [
            'question' => 'Can I cut and trim my videos?',
            'answer' => 'Yes, classer allows you to cut and trim your videos to easily share them.',
        ],
        [
            'question' => 'Is this a cloud service?',
            'answer' => 'Not yet but we are working on it ;).',
        ],
        [
            'question' => 'Does Classer use my directory from my folder file?',
            'answer' =>
                'Yes, Classer leverages the existing structure of your file folder, enabling quicker access to what you\'re seeking.',
        ],
        [
            'question' => 'Does it work with all action cameras?',
            'answer' => 'Yes and all video file formats, including .mp4, .mov, .avi',
        ],
        [
            'question' => 'I would like to contact the team, how do I do it?',
            'answer' => 'Happy to chat! Please contact us at info@classermedia.com',
        ],
        [
            'question' => 'How to turn on my GPS on my GoPro?',
            'answer' =>
                'From the main screen from GoPro, swipe down (HERO11/10/9 white, swipe left after swiping down) and tap [Preferences]. For HERO11 Black, scroll to [GPS] and turn GPS [On]. For HERO10/9 Black, scroll to [Regional], tap [GPS] and turn GPS [On].',
        ],
    ];
@endphp

<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">Common questions from users</h2>
<div id="faqs" class="grid text-left grid-cols-1 md:sp md:grid-cols-2 gap-x-12 m-auto max-w-sm md:max-w-6xl">
    @foreach ($fAq as $faq)
        <div @class([
            'mb-8' => !$loop->last,
        ])>
            <h3 class="flex items-center mb-4 mt-6 text-brand-color text-xl font-bold">
                {{ $faq['question'] }}
            </h3>
            <p class="md:max-w-xs">
                {{ $faq['answer'] }}
            </p>
        </div>
    @endforeach
</div>

<section class="mx-auto md:max-w-3xl pt-6 md:pt-12">
    @include('partials.home.available-for')
</section>