<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color mb-6">
    Our stories
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
    @foreach ($stories as $story)
        <div>
            <div class="border rounded-md flex flex-col gap-y-3 relative">
                <a href="{{ $story['permalink'] }}">
                    <img src="{{ $story['thumbnail'] }}" alt="{{ $story['title'] }}" class="w-full h-auto rounded-md" />
                    <p class="absolute bottom-0 w-full text-white m-4" >{{ $story['title'] }}</p>
                </a>
            </div>
        </div>
    @endforeach
</div>

