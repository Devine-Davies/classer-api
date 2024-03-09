<div class="sm:py-8 text-center mb-6 md:mb-8">
    <h2 class="text-5xl font-bold text-center text-brand-color mb-3">
        Our stories
    </h2>
</div>

<div class="flex justify-center gap-8 px-4 max-w-7xl m-auto">
    @foreach ($stories as $story)
        <div class="w-1/2 sm:w-1/3">
            <div class="border rounded-md flex flex-col gap-y-3 relative block">
                <a href="{{ $story['permalink'] }}">
                    <img src="{{ $story['thumbnail'] }}" alt="{{ $story['title'] }}" class="w-full h-auto" />
                    <p class="absolute bottom-0 w-full text-white m-4" >{{ $story['title'] }}</p>
                </a>
            </div>
        </div>
    @endforeach
</div>

