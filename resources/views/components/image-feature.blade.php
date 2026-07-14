@props([
    'imageSrc',
    'imageAlt' => '',
    'title',
    'description',
    'buttonLabel',
    'buttonUrl',
])

<section {{ $attributes->merge(['class' => 'relative bg-[#F6F4F1]']) }}>
    <div class="overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-[1.45fr_1fr] items-stretch">
            <img
                alt="{{ $imageAlt }}"
                class="block h-full w-full object-cover aspect-[4/5] md:aspect-[5/4]"
                src="{{ $imageSrc }}"
            />

            <article class="bg-[#F6F4F1] px-8 py-8 md:px-12 md:py-10 flex flex-col justify-center">
                <div class="mx-auto max-w-xl">
                    <div class="flex justify-center flex-col items-center">
                        <header>
                            <h2 class="text-2xl md:text-5xl lg:text-4xl text-brand-color mb-6 text-absolute not-italic font-medium leading-[108.54%] text-center">
                                {{ $title }}
                            </h2>
                        </header>

                        <p class="text-lg md:text-xl lg:text-lg mb-6 text-center">
                            {{ $description }}
                        </p>

                        <a href="{{ $buttonUrl }}" class="btn btn-lg uppercase">
                            {{ $buttonLabel }}
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
