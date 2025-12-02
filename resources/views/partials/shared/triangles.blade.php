@php
    $triangles = ['al fg sm', '', 'al fg md', 'lg', 'al fg'];
@endphp

<div class="absolute overflow-hidden top-0 left-0 w-full h-full bg-gradient-to-r from-brand-color to-brand-color z-0"
    style="filter: blur(30px);">
    <div class="bg-mountains">
        <div class="mountains">
            @foreach ($triangles as $triangle)
                <div class="triangle {{ $triangle }}"></div>
            @endforeach
        </div>

        <div class="mountains">
            @foreach ($triangles as $triangle)
                <div class="triangle {{ $triangle }}"></div>
            @endforeach
        </div>
    </div>
</div>
