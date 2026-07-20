@php
	$steps = [
		[
			'label' => 'Import all your footage from the hard drive',
			'text' => 'Connect your hard drive to Classer so your footage stays in one place and is ready to browse. You can also connect an action camera to add new footage directly.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/app-features-screenshots/feature-01.png'),
			'alt' => 'Classer Home connected to an external hard drive',
			'imagePosition' => 'left',
		],
		[
			'label' => 'See your library at a glance',
			'text' => 'View useful stats about your footage, including recordings, storage used, cameras, locations, Moments, albums and tags.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/app-features-screenshots/feature-02.png'),
			'alt' => 'Classer Home organising footage into collections',
			'imagePosition' => 'right',
		],
		[
			'label' => 'Explore every recording in detail',
			'text' => 'Watch your footage alongside its location and camera information, so you can quickly understand where, when and how it was recorded.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/app-features-screenshots/feature-03.png'),
			'alt' => 'Browse your adventures on desktop and tablet',
			'imagePosition' => 'left',
		],
		[
			'label' => 'Create and share your best Moments',
			'text' => 'Trim the part you want to keep, choose the right format, add tags and create a private link to share it with friends and family.',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/app-features-screenshots/feature-04.png'),
			'alt' => 'Browse your adventures on desktop and tablet',
			'imagePosition' => 'right',
		],
		[
			'label' => 'Access your memories across your devices',
			'text' => 'Browse and enjoy your organised video library from different devices wherever you are at home. (Mobile coming soon!)',
			'image' => Storage::disk('s3')->url('classermedia.com/assets/images/app-features-screenshots/feature-05.png'),
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
					Explore the Classer app
				</h2>
			</header>

			<x-steps-showcase :steps="$steps" />
		</div>
	</section>

    <section>
        @include('partials.banner')
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