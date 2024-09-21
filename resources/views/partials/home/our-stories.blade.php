<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">
    Our stories
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
    @foreach ($stories as $story)
        <div>
            <div class="border rounded-md flex flex-col gap-y-3 relative">
                <a href="{{ $story['permalink'] }}">
                    <img src="{{ $story['thumbnail'] }}" alt="{{ $story['alt'] }}" class="h-auto w-full rounded-md" />
                    <p class="absolute bottom-0 text-white m-4 font-bold text-2xl" >{{ $story['title'] }}</p>
                </a>
            </div>
        </div>
    @endforeach
</div>