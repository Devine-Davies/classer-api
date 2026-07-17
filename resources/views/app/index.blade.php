@php
	$steps = [
		[
			'label' => 'Connect your devices',
			'text' => 'Classer Home imports your footage directly to your hard drive. Your hard drive stores the footage, while Classer Home makes it meaningful.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step01.jpg'),
			'alt' => 'Classer Home connected to an external hard drive',
			'imagePosition' => 'left',
		],
		[
			'label' => 'Create collections',
			'text' => 'Classer Home turns raw files into meaningful collections, preserving GPS, telemetry and camera data. It works quietly in the background while you get on with your day.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step02.jpg'),
			'alt' => 'Classer Home organising footage into collections',
			'imagePosition' => 'right',
		],
		[
			'label' => 'Browse all your adventures',
			'text' => 'Browse your adventures as collections, organised by trips, days and activities in the app. Export or share them anytime on Mac, Windows and iPad.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Step03.jpg'),
			'alt' => 'Browse your adventures on desktop and tablet',
			'imagePosition' => 'left',
		],
	];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Classer App - Capture moments, not megabytes</title>
	@include('partials.meta')
</head>

<body class="antialiased bg-off-white">
	@include('partials.navigation')

	{{-- Top hero from deprecated welcome page --}}
	<section id="hero-section" class="pt-4 md:pt-6">
		<div class="mx-auto w-full max-w-7xl">
			@include('app.partials.hero')
		</div>
	</section>

	{{-- Tabs showcase --}}
	<section id="tabs-showcase-section" class="py-10 md:py-14">
		<div class="mx-auto w-full max-w-7xl px-4 md:px-6">
			@include('home.partials.tabs-showcase')
		</div>
	</section>

	{{-- Steps showcase (with dedicated image data) --}}
	<section id="steps-showcase-section" class="bg-[#fafafa] py-12 md:py-16">
		<div class="mx-auto w-full max-w-7xl px-4 md:px-6">
			<header class="mx-auto max-w-3xl text-center">
				<h2 class="text-3xl md:text-4xl lg:text-5xl text-brand-color font-medium leading-[108.54%]">
					How Classer works
				</h2>
				<p class="mt-4 text-base md:text-lg text-gray-600">
					Bring your footage out of storage and into a clean, searchable and shareable workflow.
				</p>
			</header>

			<x-steps-showcase :steps="$steps" />
		</div>
	</section>

    {{-- FAQ --}}
    <section class="mt-8 md:mt-12">
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                <div class="w-full">
                    @include('partials.f-a-q', ['faqs' => $faqs])
                </div>
            </div>
        </div>
    </section>

	@include('partials.footer')
</body>
</html>