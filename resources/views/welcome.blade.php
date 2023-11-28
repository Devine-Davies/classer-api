<?php
$trialCode = isset($_GET['trial-code']) ? $_GET['trial-code'] : '';
$trialDownloadUrl = '/downloads/sample.pdf';
?>
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="description"
        content="A space for outdoor people who love to capture their adventures with action cameras. The platform for your post-recordings. A journal, but a journal driven by data, created to be shared and built to get you motivated." />
    <meta name="keywords"
        content="video management tool, video library, video library management, video library management software, video management software, top video management tool, best video management tool, save videos, video storage, video storage solutions, media management, media asset management, media storage solutions, digital asset manager for videos, personal digital asset manager, storage videos, storing videos, video storage, free video storage, best storage for videos, unlimited video storage, best video storage, synology storage, synology memories, synology photos, synology manager, gopro software, gopro cloud services, gopro app, gopro hero 9, gopro 9, gopro hero 11, gopro hero 10, gopro hero 8, gopro camera, gopro app desktop, gopro without subscription, gopro upload to cloud, gopro studio, gopro quik desktop, gopro quik mac, gopro quik, gopro app for windows, gopro quik for macbook, gopro app for mac, gopro desktop app, gopro video, desktop quik app, gopro app pc, gopro subscription, quik, insta360 app, insta360 desktop app, VIRB 360, digikam, Davinci Resolve, action cameras, action camera, action camera software, Digital Asset Management Systems (DAM), Video Management Software (VMS), Asset management, Classer, Classer Media, [f](https://github.com/gaumin/gopr,fil,manager)ile manager, Akaso, Veho, dji, insta360, sjcam, drift, cambox, insta360 software, highlights videos, mountain biking, telemetry gopro, telemetry insta360, outdoor sports, photostructure, photoprism, telemetry extractor, external hard drive, portable hard drive, SSD card, sd card, Pos,recording, dashware, relive app, strava app" />
    <meta name="author" content="Classer" />
    <meta name="robots" content="index, follow" />
    <meta name="language" content="English" />
    <meta name="viewport" content="width=device-width, initial-scale=1,
      shrink-to-fit=no" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/assets/images/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/assets/images/favicon-16x16.png') }}" />
    <link rel="stylesheet" href="{{ asset('/build/tailwind.css') }}" />
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <title>Classer</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QRT27E0GVR"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag("js", new Date());
        gtag("config", "G-QRT27E0GVR");
    </script>

    <!-- Smartlook -->
    <script type="text/javascript">
        window.smartlook ||
            (function(d) {
                var o = (smartlook = function() {
                        o.api.push(arguments);
                    }),
                    h = d.getElementsByTagName("head")[0];
                var c = d.createElement("script");
                o.api = new Array();
                c.async = true;
                c.type = "text/javascript";
                c.charset = "utf-8";
                c.src = "https://web-sdk.smartlook.com/recorder.js";
                h.appendChild(c);
            })(document);
        smartlook("init", "4204de3c294ccb5e98ccd7aeafc4f455163babdd", {
            region: "eu",
        });
    </script>
    @vite('resources/css/app.css')
    @vite(['resources/js/app.js'])
</head>

<body class="antialiased" trial-code="<?php echo $trialCode; ?>">
    <!-- Navigation -->
    <section id="nav" class="w-full sticky top-0 bg-white z-50">
        <div class="mx-auto p-2 flex flex-wrap items-center justify-center md:justify-start max-w-7xl m-auto">
            <img class="py-2 w-12 md:w-8" src="{{ asset('/assets/images/logos/classer-logo.svg') }}" alt="" />
            <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/logos/classer-text.svg') }}" alt="" />
        </div>
    </section>
    <!-- / Navigation -->

    <!-- Hero -->
    <section class="hero">
        <div class="text-center pt-2">
            <h2 class="text-4xl md:text-6xl lg:text-7xl mt-4">
                Remember the journey?
                <span
                    class="mb-4 mt-2 text-brand-color font-extrabold tracking-tight leading-none block text-4xl md:text-6xl lg:text-7xl">Relive
                    it all.</span>
            </h2>
            <p class="text-1xl md:text-2xl mt-4 mb-8">
                Make the most of your videos and drone recordings
            </p>
            <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                class="btn inline font-semibold text-white py-3 px-8 rounded-full">
                Get Classer
            </a>
            <a aria-label="Discover more" href="#features"
                class="inline text-brand-color font-semibold text-white py-3 px-8 rounded-full">
                Discover more
            </a>
        </div>
        <div id="carousel" class="relative m-4 mt-12 xl:mt-16">
            <img src="{{ asset('/assets/images/hero-image-2.png') }}" class="opacity-0 w-full max-w-7xl" alt="...">
            <div id="slides" class="absolute w-full max-w-7xl top-0 left-1/2 -translate-x-1/2 ">
                <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
                    <img src="{{ asset('/assets/images/hero-image-1.png') }}" alt="...">
                </div>
                <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
                    <img src="{{ asset('/assets/images/hero-image-2.png') }}" alt="...">
                </div>
                <div class="h-full w-full absolute opacity-0 transition-opacity duration-700 ease-in-out">
                    <img src="{{ asset('/assets/images/hero-image-3.png') }}" alt="...">
                </div>
            </div>
            <div id="indicators" class="absolute z-10 flex gap-6 justify-center w-full mt-8">
                <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="true"
                    aria-label="Slide 1" data-carousel-slide-to="0"></button>
                <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="false"
                    aria-label="Slide 2" data-carousel-slide-to="1"></button>
                <button type="button" class="w-3 h-3 rounded-full bg-zinc-300 hover:bg-zinc-950" aria-current="false"
                    aria-label="Slide 3" data-carousel-slide-to="2"></button>
            </div>
        </div>
    </section>
    <!-- / Hero -->

    <!-- Features -->
    <section id="features" class="overflow-hidden w-full mt-8 p-4 sm:p-8">
        <div class="m-auto max-w-7xl"></div>
    </section>
    <!-- / Features -->

    <!-- for all -->
    <section class="mt-16 md:mt-24 lg:mt-32" style="background-color: #f4f2ea">
        <div class="mx-auto max-w-7xl py-8 md:py-16 px-4 flex-wrap center justify-center">
            <h3 class="font-bold text-brand-color text-center sm:px-16 xl:px-48 text-2xl md:text-4xl max-w-5xl mx-auto">
                Made for all your activities
            </h3>
            <p class="text-base text-center max-w-2xl mx-auto my-3 mb-8">Using a single, uniform and elegant interface.
                Its unified UI design and innovative features make exploring your favorite footage faster, more
                intuitive and fun.</p>
            <div class="flex">
                <img class="m-auto" src="{{ asset('/assets/images/activities/karting.svg') }}" alt="karting-activity">
                <img class="m-auto" src="{{ asset('/assets/images/activities/kayaking.svg') }}"
                    alt="kayaking-activity">
                <img class="m-auto" src="{{ asset('/assets/images/activities/motorcycle.svg') }}"
                    alt="motorcycle-activity">
                <img class="hidden md:block m-auto" src="{{ asset('/assets/images/activities/mtb.svg') }}"
                    alt="mtb-activity">
                <img class="hidden md:block m-auto" src="{{ asset('/assets/images/activities/skiing.svg') }}"
                    alt="skiing-activity">
                <img class="hidden md:block m-auto" src="{{ asset('/assets/images/activities/travel.svg') }}"
                    alt="travel-activity">
                <img class="hidden lg:block m-auto" src="{{ asset('/assets/images/activities/surf.svg') }}"
                    alt="surf-activity">
            </div>
        </div>
    </section>
    <!-- / Actions -->

    <!-- How it works -->
    <section class="bg-off-white">
        <div class="mx-auto max-w-7xl py-8 md:py-16 px-6">
            <section>
                <h3 class="text-4xl font-bold text-center text-brand-color mb-8">How Classer works</h3>
                <div class="flex gap-8 flex-col md:flex-row justify-between w-full relative mb-6 md:mt-16 lg:mt-16">
                    <div class="hWv4NA">
                        <div
                            style="clip-path: path(&quot;M 5.10504 181.911 C 31.5311 141.872 51.8512 115.389 65.4647 99.3103 C 76.5756 86.3842 86.5855 75.665 96.0949 67.1527 C 104.103 59.9015 109.308 55.803 118.417 50.4433 C 132.231 42.2463 151.55 30.8966 171.069 26.1675 C 195.393 20.1773 227.725 18.601 253.45 23.0148 C 276.273 26.798 299.496 37.8325 317.914 47.6059 C 331.828 54.8571 342.638 62.7389 354.45 72.197 C 365.861 81.3399 374.37 89.2217 387.683 102.778 C 408.103 123.586 443.638 169.931 464.759 189.478 C 479.073 202.719 488.382 209.97 501.395 216.591 C 515.91 224.158 531.925 228.571 548.041 230.778 C 565.258 233.3 584.878 232.67 601.794 229.202 C 616.909 226.049 629.722 221.32 644.737 212.808 C 661.654 203.35 683.475 186.325 698.29 173.084 C 709 163.31 715.707 155.429 725.717 143.764 C 738.329 128.946 756.747 107.507 767.858 89.8522 C 775.666 77.5566 781.071 66.5222 787.077 53.2808 C 792.783 40.67 799.99 14.1872 803.193 12.6108 C 804.394 11.9803 805.596 14.1872 805.896 16.0788 C 806.196 17.9704 805.696 22.3842 805.195 23.33 C 804.595 24.5911 802.693 23.6453 802.292 21.7537 C 801.892 19.8621 802.493 14.1872 802.993 12.9261 C 803.393 11.665 804.394 11.9803 804.895 12.6108 C 805.395 13.2414 805.896 15.7635 805.996 17.6552 C 806.096 19.2315 805.796 20.8079 805.295 23.0148 C 803.794 29.3202 798.689 41.931 794.184 52.335 C 788.078 66.5222 780.871 82.9163 771.362 98.3645 C 757.548 120.749 733.024 149.123 717.909 166.148 C 707.098 178.443 699.191 185.695 689.581 193.892 C 679.771 202.404 668.16 210.916 659.551 216.591 C 653.045 221.005 649.341 223.212 642.334 226.68 C 631.724 231.724 616.309 238.975 601.794 241.813 C 585.178 245.281 565.258 245.911 547.941 243.389 C 531.625 241.182 515.209 236.768 500.494 228.887 C 487.281 221.951 477.872 214.7 463.458 201.143 C 442.337 181.596 407.102 135.882 386.782 115.074 C 373.469 101.517 364.46 93.3202 353.549 84.4926 C 343.139 75.9803 335.031 69.6749 322.619 62.7389 C 304.2 52.6502 276.573 40.0394 253.45 35.6256 C 230.327 31.2118 200.999 34.3645 183.882 36.5714 C 173.772 37.8325 168.566 39.4089 159.958 42.5616 C 149.848 46.3448 137.336 52.0197 126.925 58.0099 C 117.416 63.6847 108.107 70.3054 99.6985 77.2414 C 92.3913 83.5468 87.5865 87.9606 79.2783 97.1034 C 66.0652 111.606 41.6411 142.187 28.328 160.473 C 19.5193 172.768 10.5104 193.576 6.70662 193.576 C 5.40534 193.576 4.20415 191.369 4.00395 189.478 C 3.80376 187.586 5.10504 181.911 5.10504 181.911&quot;); background: rgb(10, 63, 77); width: 810px; height: 256px; transform: scale(0.999012, 0.317188); transform-origin: 0px 0px; touch-action: pan-x pan-y pinch-zoom;">
                        </div>
                    </div>
                    <div class="relative flex items-center md:items-start flex-col flex-1">
                        <img class="w-28 h-28 md:w-32 md:h-32 lg:w-44 lg:h-44"
                            src="{{ asset('/assets/images/how-it-works/sync.svg') }}" alt="surf-activity">
                        <p class="text-center mt-4 md:text-left max-w-xs font-semibold md:mt-3">Sync from your external
                            hard drives, SD cards, or any
                            other storage device.</p>
                    </div>
                    <div class="relative flex items-center flex-col">
                        <img class="mx-auto w-28 h-28 md:w-32 md:h-32 lg:w-44 lg:h-44"
                            src="{{ asset('/assets/images/how-it-works/app.svg') }}" alt="surf-activity">
                        <p class="text-center mt-4 md:text-left mx-auto max-w-xs font-semibold md:mt-3">Designed for
                            Desktop, built for you</p>
                    </div>
                    <div class="relative flex items-center md:items-end flex-col flex-1">
                        <img class="w-28 h-28 md:w-32 md:h-32 lg:w-44 lg:h-44"
                            src="{{ asset('/assets/images/how-it-works/video.svg') }}" alt="surf-activity">
                        <p class="text-center mt-4 md:text-end max-w-xs font-semibold md:mt-3">Automatically recognise
                            your
                            folder
                            structure, add it to Classer and let’s the fun begin!</p>
                    </div>
                </div>
            </section>

            <section class="text-center mt-8 lg:mt-12">
                <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                    class=" bg-brand-color text-white inline-flex  font-semibold justify-center items-center py-3 px-8 text-base rounded-full hover:bg-opacity-20">
                    Get Classer
                </a>

                <div class="flex justify-center mt-8 gap-4">
                    <a aria-label="Reddit" href="https://www.reddit.com/r/classer" target="_blank">
                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -3 24 24" width="24"
                            height="24" fill="#0a3f4d">
                            <path
                                d="M19.986 8.029a2.51 2.51 0 0 0-4.285-1.771c-1.404-.906-3.197-1.483-5.166-1.573a2.734 2.734 0 0 1 1.028-2.139 2.735 2.735 0 0 1 2.315-.539l.112.025c0 .028-.004.056-.004.084a2.095 2.095 0 1 0 .328-1.121L14.113.95a3.812 3.812 0 0 0-3.228.752 3.812 3.812 0 0 0-1.433 2.983c-1.97.09-3.762.667-5.165 1.572a2.51 2.51 0 1 0-2.94 3.994c-.061.31-.093.628-.093.952 0 3.606 3.912 6.53 8.74 6.53 4.826 0 8.739-2.924 8.739-6.53 0-.324-.032-.641-.093-.952a2.508 2.508 0 0 0 1.346-2.222zm-3.905-6.925a1.013 1.013 0 0 1 0 2.025 1.013 1.013 0 0 1 0-2.025zM1.083 8.03c0-.787.64-1.427 1.427-1.427.337 0 .646.118.89.314-.763.655-1.354 1.425-1.721 2.27a1.423 1.423 0 0 1-.596-1.157zm14.442 6.923c-1.465 1.095-3.43 1.698-5.532 1.698s-4.067-.603-5.531-1.698c-1.37-1.023-2.125-2.355-2.125-3.75 0-1.394.754-2.725 2.125-3.75C5.926 6.359 7.89 5.757 9.993 5.757c2.103 0 4.067.602 5.532 1.697 1.37 1.024 2.125 2.355 2.125 3.75 0 1.394-.755 2.726-2.125 3.75zm2.782-5.767c-.367-.845-.958-1.614-1.721-2.269.244-.196.554-.314.89-.314.787 0 1.427.64 1.427 1.427 0 .476-.235.898-.596 1.156z" />
                            <circle cx="6.801" cy="9.678" r="1.143" />
                            <circle cx="13.185" cy="9.678" r="1.143" />
                            <path
                                d="M12.701 12.455a4.357 4.357 0 0 1-2.94 1.138 4.325 4.325 0 0 1-3.195-1.39.541.541 0 1 0-.793.738 5.47 5.47 0 0 0 3.988 1.735 5.437 5.437 0 0 0 3.67-1.421.541.541 0 1 0-.73-.8z" />
                        </svg>
                    </a>

                    <a aria-label="Instagram" href="https://www.instagram.com/classermedia_" target="_blank">
                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24"
                            height="24" fill="#0a3f4d">
                            <path
                                d="M14.017 0h-8.07A5.954 5.954 0 0 0 0 5.948v8.07a5.954 5.954 0 0 0 5.948 5.947h8.07a5.954 5.954 0 0 0 5.947-5.948v-8.07A5.954 5.954 0 0 0 14.017 0zm3.94 14.017a3.94 3.94 0 0 1-3.94 3.94h-8.07a3.94 3.94 0 0 1-3.939-3.94v-8.07a3.94 3.94 0 0 1 3.94-3.939h8.07a3.94 3.94 0 0 1 3.939 3.94v8.07z" />
                            <path
                                d="M9.982 4.819A5.17 5.17 0 0 0 4.82 9.982a5.17 5.17 0 0 0 5.163 5.164 5.17 5.17 0 0 0 5.164-5.164A5.17 5.17 0 0 0 9.982 4.82zm0 8.319a3.155 3.155 0 1 1 0-6.31 3.155 3.155 0 0 1 0 6.31z" />
                            <circle cx="15.156" cy="4.858" r="1.237" />
                        </svg>
                    </a>
                </div>
            </section>

            <section id="tutorials" class="mt-16">
                <h3 class="text-4xl font-bold text-center text-brand-color mb-8">Explore our guides</h3>
                <div id="tutorial-items"
                    class="mb-6 md:mt-16 lg:mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                </div>
            </section>
        </div>
    </section>
    <!-- / How it works -->

    <!-- Our Plan -->
    <section>
        <div class="mx-auto py-8 md:py-16 px-6">
            <!-- font-bold text-center sm:px-16 xl:px-48 text-2xl md:text-4xl max-w-5xl mx-auto -->
            <h3 class="text-4xl font-bold text-center text-brand-color">
                Our plan
            </h3>
            <p class="text-base text-center mx-auto mt-3 max-w-md md:max-w-3xl">
                We are currently opening Classer for free for all users who would like to
                become a team beta tester for all our new features.
            </p>
        </div>

        <div class="mt-6 max-w-7xl mx-auto">
            <div class="flex justify-center flex-wrap md:flex-nowrap gap-8 mx-8 my-14 -mt-4 m-auto md:gap-14">
                <div class="bg-brand-color text-white p-4 w-full text-center relative rounded-md max-w-md">
                    <div
                        class="rounded-full w-16 h-16 bg-gold text-white inline-flex justify-center items-center shadow-md absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 scale-110">
                        <p class="font-small font-semibold uppercase tracking-widest">Free</p>
                    </div>
                    <h2 class="text-xl font-bold my-4 tracking-widest">CLASSER</h2>
                    <hr class="mb-4 m-auto border-gray-400 w-4/5" />
                    <ul class="m-auto list-none flex flex-col gap-y-4 w-4/5">
                        <li class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="white">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Organizing, Telemetry, GPS
                        </li>
                        <li class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="white">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Clipping, Trimming, Merging
                        </li>
                        <li class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="white">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            And much more
                        </li>
                    </ul>
                    <a aria-label="Download" href="#" data-modal-toggle="modal-toggle"
                        class="
          bg-white
          text-brand-color
          scale-90
          mt-4
          inline-flex 
          font-semibold
          justify-center
          items-center
          py-3
          px-8
          text-base
          rounded-full
          hover:bg-opacity-20
        ">Get
                        Classer</a>
                </div>

                <div class="bg-brand-color text-white p-4 w-full text-center relative rounded-md max-w-md">
                    <h2 class="text-xl font-bold  my-4 tracking-widest">PRO CLASSER</h2>
                    <hr class="mb-4 m-auto border-gray-400 w-4/5" />
                    <ul class="m-auto list-none flex flex-col gap-2 w-4/5">
                        <li class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="white">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            AI
                        </li>
                        <li class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="white">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Cloud services
                        </li>
                    </ul>
                    <p
                        class="tracking-widest md:absolute font-semibold md:bottom-0 m-6 mt-8 md:left-1/2 md:-translate-x-1/2">
                        Coming soon...</p>
                </div>
            </div>
        </div>
    </section>
    <!-- / Our Plan -->

    <!-- Works With -->
    <section>
        <div class="mx-auto px-2 my-12 md:my-18 lg:my-24">
            <div class="mx-auto max-w-screen-xl sm:py-8">
                <h2 class="text-2xl font-bold text-center text-brand-color mb-6 md:mb-8">
                    Works with your favourite brands
                </h2>
                <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-7">
                    <div class="h-16 flex align-center justify-center">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/akaso.png') }}"
                            alt="" />
                    </div>

                    <div class="h-16 flex align-center justify-center">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/sjcam.png') }}"
                            alt="" />
                    </div>

                    <div class="h-16 flex align-center justify-center">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/dji.png') }}"
                            alt="" />
                    </div>

                    <div class="h-16 flex align-center justify-center">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/go-pro.png') }}"
                            alt="" />
                    </div>

                    <div class="h-16 flex align-center justify-center">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/insta360.png') }}"
                            alt="" />
                    </div>

                    <div class="flex h-16 align-center justify-center md:hidden lg:flex">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/nikon.png') }}"
                            alt="" />
                    </div>

                    <div class="hidden h-16 align-center justify-center md:hidden lg:flex">
                        <img class="m-auto w-6/12" src="{{ asset('/assets/images/logos/veho.png') }}"
                            alt="" />
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- / Works With -->

    <!-- for all -->
    <section class="bg-off-white community mt-10 text-center relative py-8 overflow-hidden max-w-full"
        style="overflow: hidden;">
        <div class="mx-auto py-8 md:py-16 px-4 flex-wrap center justify-center relative z-10">
            <h3
                class="text-brand-color font-bold text-center sm:px-16 xl:px-48 text-2xl md:text-4xl max-w-5xl mx-auto">
                Join our community
            </h3>
            <p class="text-base text-center max-w-3xl mx-auto mt-3 mb-8">Share your footage with others and get support
                if needed!</p>
            <a aria-label="Community"
                class="btn bg-gold text-brand-color mt-4 inline font-semibold text-white py-3 px-8 rounded-full"
                href="https://www.reddit.com/r/classer" target="_blank">
                Join our community
            </a>
        </div>
    </section>
    <!-- / Actions -->

    <!-- How it works -->
    <section class="bg-off-white hidden">
        <div class="mx-auto py-8 p-4 sm:p-8 md:py-16 px-12">
            <div class="text-center">
                <h2 class="font-bold text-center text-brand-color mb-6 text-3xl md:text-8xl">
                    Relive your journey
                </h2>
                <p>
                    Explore all your adventures and get excited for the journey ahead!
                </p>
            </div>
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-16 lg:gap-12 md:space-y-0 mt-12">
                <div>
                    <div class="w-24 h-24 m-auto bg-gray rounded-full">
                        <img src="{{ asset('/assets/images/steps/step1.svg') }}" alt="" />
                    </div>
                    <h3 class="mt-6 text-xl text-center font-bold text-brand-color">
                        Look at your achievements and your precious moments
                    </h3>
                </div>

                <div>
                    <div class="w-24 h-24 m-auto bg-gray rounded-full">
                        <img src="{{ asset('/assets/images/steps/step2.svg') }}" alt="" />
                    </div>
                    <h3 class="mt-6 text-xl text-center font-bold text-brand-color">
                        Embrace your stand out moments, the good and the bad.
                    </h3>
                </div>

                <div>
                    <div class="w-24 h-24 m-auto flex justify-center items-center bg-gray rounded-full">
                        <img src="{{ asset('/assets/images/steps/step3.svg') }}" alt="" />
                    </div>
                    <h3 class="mt-6 text-xl text-center font-bold text-brand-color">
                        Keep challenging yourself and document your progress.
                    </h3>
                </div>
            </div>
        </div>
    </section>
    <!-- / How it works -->

    <!-- Actions -->
    <section class="brand-color-bg hidden">
        <div class="mx-auto py-8 md:py-8 px-4">
            <p class="text-center font-bold mb-8 text-white sm:px-16 xl:px-48 text-2xl md:text-4xl">
                Make the most of your action camera recordings
            </p>
            <div class="text-center">
                <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                    class="inline-flex font-semibold bg-white justify-center items-center py-3 px-8 text-base font-medium text-center text-brand-color rounded-full">
                    Get Classer
                </a>
            </div>

            <div class="flex justify-center mt-6 gap-4">
                <a aria-label="Reddit" href="https://www.reddit.com/r/classer" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -3 24 24" width="24" height="24"
                        fill="currentColor">
                        <path
                            d="M19.986 8.029a2.51 2.51 0 0 0-4.285-1.771c-1.404-.906-3.197-1.483-5.166-1.573a2.734 2.734 0 0 1 1.028-2.139 2.735 2.735 0 0 1 2.315-.539l.112.025c0 .028-.004.056-.004.084a2.095 2.095 0 1 0 .328-1.121L14.113.95a3.812 3.812 0 0 0-3.228.752 3.812 3.812 0 0 0-1.433 2.983c-1.97.09-3.762.667-5.165 1.572a2.51 2.51 0 1 0-2.94 3.994c-.061.31-.093.628-.093.952 0 3.606 3.912 6.53 8.74 6.53 4.826 0 8.739-2.924 8.739-6.53 0-.324-.032-.641-.093-.952a2.508 2.508 0 0 0 1.346-2.222zm-3.905-6.925a1.013 1.013 0 0 1 0 2.025 1.013 1.013 0 0 1 0-2.025zM1.083 8.03c0-.787.64-1.427 1.427-1.427.337 0 .646.118.89.314-.763.655-1.354 1.425-1.721 2.27a1.423 1.423 0 0 1-.596-1.157zm14.442 6.923c-1.465 1.095-3.43 1.698-5.532 1.698s-4.067-.603-5.531-1.698c-1.37-1.023-2.125-2.355-2.125-3.75 0-1.394.754-2.725 2.125-3.75C5.926 6.359 7.89 5.757 9.993 5.757c2.103 0 4.067.602 5.532 1.697 1.37 1.024 2.125 2.355 2.125 3.75 0 1.394-.755 2.726-2.125 3.75zm2.782-5.767c-.367-.845-.958-1.614-1.721-2.269.244-.196.554-.314.89-.314.787 0 1.427.64 1.427 1.427 0 .476-.235.898-.596 1.156z" />
                        <circle cx="6.801" cy="9.678" r="1.143" />
                        <circle cx="13.185" cy="9.678" r="1.143" />
                        <path
                            d="M12.701 12.455a4.357 4.357 0 0 1-2.94 1.138 4.325 4.325 0 0 1-3.195-1.39.541.541 0 1 0-.793.738 5.47 5.47 0 0 0 3.988 1.735 5.437 5.437 0 0 0 3.67-1.421.541.541 0 1 0-.73-.8z" />
                    </svg>
                </a>

                <a aria-label="Twitter" href="https://www.instagram.com/classermedia_" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24"
                        fill="currentColor">
                        <path
                            d="M14.017 0h-8.07A5.954 5.954 0 0 0 0 5.948v8.07a5.954 5.954 0 0 0 5.948 5.947h8.07a5.954 5.954 0 0 0 5.947-5.948v-8.07A5.954 5.954 0 0 0 14.017 0zm3.94 14.017a3.94 3.94 0 0 1-3.94 3.94h-8.07a3.94 3.94 0 0 1-3.939-3.94v-8.07a3.94 3.94 0 0 1 3.94-3.939h8.07a3.94 3.94 0 0 1 3.939 3.94v8.07z" />
                        <path
                            d="M9.982 4.819A5.17 5.17 0 0 0 4.82 9.982a5.17 5.17 0 0 0 5.163 5.164 5.17 5.17 0 0 0 5.164-5.164A5.17 5.17 0 0 0 9.982 4.82zm0 8.319a3.155 3.155 0 1 1 0-6.31 3.155 3.155 0 0 1 0 6.31z" />
                        <circle cx="15.156" cy="4.858" r="1.237" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
    <!-- / Actions -->

    <!-- FAQ's -->
    <section>
        <div class="mx-auto mb-6 md:max-w-5xl p-4 md:p-8 md:py-12">
            <h2 class="text-4xl mt-4 font-bold text-center text-brand-color">FAQ's</h2>
            <div id="faqs"
                class="grid pt-8 text-left grid-cols-1 md:sp md:grid-cols-2 gap-x-36 m-auto max-w-sm md:max-w-6xl">
            </div>
        </div>
    </section>
    <!-- / FAQ's-->

    <!-- Available for -->
    <section class="bg-off-white p-4">
        <div class="mx-auto md:max-w-3xl py-6 pb-10 md:py-16">
            <h2 class="font-bold text-brand-color text-center sm:px-16 xl:px-48 text-2xl md:text-4xl mx-auto">It's
                available for</h2>
            <p class="m-auto mt-3 text-center max-w-md md:max-w-lg">You can register Classer on your phone too. An
                email will be sent to you so you can download on your desktop at a later time.</p>

            <div class="flex justify-center mt-4 gap-4">
                <a aria-label="Mac Download" href="#" data-modal-toggle="modal-toggle"
                    class="btn mt-4 inline font-semibold text-white py-3 px-8 rounded-full">
                    Mac
                </a>
                <a aria-label="Windows Download" href="#" data-modal-toggle="modal-toggle"
                    class="btn mt-4 inline font-semibold text-white py-3 px-8 rounded-full">
                    Windows
                </a>
                <a aria-label="Linux Download" href="#" data-modal-toggle="modal-toggle"
                    class="btn mt-4 inline font-semibold text-white py-3 px-8 rounded-full pointer-events-none opacity-60">
                    Linux
                </a>
            </div>
        </div>
    </section>
    <!-- / Available for -->

    <!-- OS -->
    <section class="hidden">
        <div class="mx-auto mb-8 md:p-18 px-12">
            <p class="font-bold text-brand-color text-center sm:px-16 xl:px-48 text-2xl md:text-4xl mx-auto">Available
                for</p>
            <div class="grid grid-cols-3 m-auto max-w-xs gap-10 px-12 py-8" style="max-width: 275px">
                <img src="{{ asset('/assets/images/os/mac.svg') }}" alt="" />
                <img style="opacity:0.3;" title="Coming soon" src="{{ asset('/assets/images/os/linux.svg') }}"
                    alt="" />
                <img style="opacity:0.3;" title="Coming soon" src="{{ asset('/assets/images/os/windows.svg') }}"
                    alt="" />
            </div>
        </div>
    </section>
    <!-- / OS -->

    <!-- Actions -->
    <section class="brand-color-bg">
        <div class="mx-auto py-8 md:py-8 px-4">
            <p
                class="text-center font-bold mb-8 m-auto max-w-sm text-white sm:px-16 xl:px-48 text-2xl md:text-4xl md:max-w-5xl">
                Make the most of your action camera recordings
            </p>
            <div class="text-center">
                <a aria-label="Download" href="#" data-modal-toggle="modal-toggle"
                    class="inline-flex font-semibold bg-white justify-center items-center py-3 px-8 text-base font-medium text-center text-brand-color rounded-full">
                    Get Classer
                </a>
            </div>

            <div class="flex justify-center mt-6 gap-4">
                <a aria-label="Reddit" href="https://www.reddit.com/r/classer" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -3 24 24" width="24" height="24"
                        fill="currentColor">
                        <path
                            d="M19.986 8.029a2.51 2.51 0 0 0-4.285-1.771c-1.404-.906-3.197-1.483-5.166-1.573a2.734 2.734 0 0 1 1.028-2.139 2.735 2.735 0 0 1 2.315-.539l.112.025c0 .028-.004.056-.004.084a2.095 2.095 0 1 0 .328-1.121L14.113.95a3.812 3.812 0 0 0-3.228.752 3.812 3.812 0 0 0-1.433 2.983c-1.97.09-3.762.667-5.165 1.572a2.51 2.51 0 1 0-2.94 3.994c-.061.31-.093.628-.093.952 0 3.606 3.912 6.53 8.74 6.53 4.826 0 8.739-2.924 8.739-6.53 0-.324-.032-.641-.093-.952a2.508 2.508 0 0 0 1.346-2.222zm-3.905-6.925a1.013 1.013 0 0 1 0 2.025 1.013 1.013 0 0 1 0-2.025zM1.083 8.03c0-.787.64-1.427 1.427-1.427.337 0 .646.118.89.314-.763.655-1.354 1.425-1.721 2.27a1.423 1.423 0 0 1-.596-1.157zm14.442 6.923c-1.465 1.095-3.43 1.698-5.532 1.698s-4.067-.603-5.531-1.698c-1.37-1.023-2.125-2.355-2.125-3.75 0-1.394.754-2.725 2.125-3.75C5.926 6.359 7.89 5.757 9.993 5.757c2.103 0 4.067.602 5.532 1.697 1.37 1.024 2.125 2.355 2.125 3.75 0 1.394-.755 2.726-2.125 3.75zm2.782-5.767c-.367-.845-.958-1.614-1.721-2.269.244-.196.554-.314.89-.314.787 0 1.427.64 1.427 1.427 0 .476-.235.898-.596 1.156z" />
                        <circle cx="6.801" cy="9.678" r="1.143" />
                        <circle cx="13.185" cy="9.678" r="1.143" />
                        <path
                            d="M12.701 12.455a4.357 4.357 0 0 1-2.94 1.138 4.325 4.325 0 0 1-3.195-1.39.541.541 0 1 0-.793.738 5.47 5.47 0 0 0 3.988 1.735 5.437 5.437 0 0 0 3.67-1.421.541.541 0 1 0-.73-.8z" />
                    </svg>
                </a>

                <a aria-label="instagram" href="https://www.instagram.com/classermedia_" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24"
                        fill="currentColor">
                        <path
                            d="M14.017 0h-8.07A5.954 5.954 0 0 0 0 5.948v8.07a5.954 5.954 0 0 0 5.948 5.947h8.07a5.954 5.954 0 0 0 5.947-5.948v-8.07A5.954 5.954 0 0 0 14.017 0zm3.94 14.017a3.94 3.94 0 0 1-3.94 3.94h-8.07a3.94 3.94 0 0 1-3.939-3.94v-8.07a3.94 3.94 0 0 1 3.94-3.939h8.07a3.94 3.94 0 0 1 3.939 3.94v8.07z" />
                        <path
                            d="M9.982 4.819A5.17 5.17 0 0 0 4.82 9.982a5.17 5.17 0 0 0 5.163 5.164 5.17 5.17 0 0 0 5.164-5.164A5.17 5.17 0 0 0 9.982 4.82zm0 8.319a3.155 3.155 0 1 1 0-6.31 3.155 3.155 0 0 1 0 6.31z" />
                        <circle cx="15.156" cy="4.858" r="1.237" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
    <!-- / Actions -->

    <!-- Footer  -->
    <footer class="bg-off-white w-full px-4">
        <div class="max-w-7xl m-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 py-8">
            <div class="flex items-center justify-center">
                <img class="py-2 w-14" src="{{ asset('/assets/images/logos/classer-logo.svg') }}" alt="" />
                <img class="py-2 px-4 w-40" src="{{ asset('/assets/images/logos/classer-text.svg') }}"
                    alt="" />
            </div>
            <div class="text-center mt-2 flex items-center justify-center">
                <a aria-label="Contact Email" href="mailto:info@classermedia.com"
                    class="ml-4 text-blue-500 hover:underline">
                    info@classermedia.com
                </a>
            </div>
            <div
                class="text-center mt-8 md:mt-0 md:text-right flex items-center justify-center gap-16 md:gap-4 md:col-start-2 lg:col-start-3">
                <a aria-label="Instagram" class="text-blue-500 hover:underline"
                    href="https://www.instagram.com/classermedia_" target="_blank">
                    <img src="{{ asset('/assets/images/jam-icons/icons/instagram.svg') }}" alt=""
                        class="inline" />
                    Instagram
                </a>
                <a aria-label="Reddit" class="text-blue-500 hover:underline" href="https://www.reddit.com/r/classer"
                    target="_blank">
                    <img src="{{ asset('/assets/images/jam-icons/icons/reddit.svg') }}" alt=""
                        class="inline" />
                    Reddit
                </a>
            </div>
        </div>
    </footer>
    <!-- / Footer -->

    <!-- Main modal -->
    <article tabindex="-1" data-modal="modal-toggle"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center">
        <div class="relative p-4 m-auto w-1/1 max-w-2xl">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white"
                    data-modal-toggle="modal-toggle">
                    <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="" />
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="py-6 px-6 px-8">
                    <script>
                        document.addEventListener('htmx:afterRequest', function(evt) {
                            if (evt.detail.successful != true) {
                                return alert("Register error, please try again ");
                            }

                            document.getElementById("register-success").classList.remove("hidden");
                            document.getElementById("register-form").classList.add("hidden");
                        });
                    </script>

                    <form id="register-form" class="space-y-6" hx-post="api/auth/register" hx-indicator="#spinner">
                        {{-- hx-on="htmx:afterRequest: onRegisterSuccess();" --}}
                        @csrf

                        <div class="text-center mb-8">
                            <h3 class="mb-4 text-xl font-bold text-brand-color">
                                Welcome to Classer
                            </h3>
                            <p>
                                Signup now to get early access, we will sent out a download link to your email along
                                with a
                                code to start accessing Classer.
                            </p>
                        </div>
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium">Name</label>
                            <input type="text" name="name" id="name"
                                class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                                placeholder="Jane Doe" required />
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                            <input type="email" name="email" id="email"
                                class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                                placeholder="yourEmail@example.com" required />
                        </div>
                        <div class="flex justify-between">
                            <div class="flex items-start"></div>
                            <input type="submit"
                                class="g-recaptcha btn inline-flex font-semibold text-white justify-center items-center py-3 px-5 text-base font-medium text-center text-gray-900 rounded-full" />
                            {{-- data-sitekey="6LeT-wwmAAAAAL64va5W33XKEhALIBLnjeDv_FtL" data-callback='onSubmit' data-action='submit' --}}
                        </div>
                    </form>

                    <div id="register-success" class="py-6 px-6 px-8 hidden">
                        <h3 class="mb-4 text-center text-xl font-bold text-brand-color">
                            Glad to have you on board 🎉
                        </h3>
                        <p class="text-center">To complete the registration process, please check your email for
                            Classer for your download link and access code. You will then be able to start using Classer
                            and make the most of your recordings.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <!-- / Main modal -->

    <!-- Modal thankyou for registering -->
    <article tabindex="-1" id="thank-you-modal" data-modal="modal-toggle-registering"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center">
        <div class="relative p-4 m-auto w-1/1 max-w-lg">
            <div class="relative p-4 bg-white rounded-lg shadow">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white"
                    data-modal-toggle="modal-toggle-registering">
                    <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="" />
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="py-6 px-6 px-8">
                    <h3 class="mb-4 text-center text-xl font-bold text-brand-color">
                        Glad to have you on board 🎉
                    </h3>
                    <p class="text-center">Check your email for Classer for your download link and access code.</p>
                </div>
            </div>
        </div>
    </article>
    <!-- / Modal thankyou for registering -->

    <!-- Download modal -->
    <article tabindex="-1" id="trial-download" data-modal="modal-trial-download"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 
  bottom-0 z-50 h-full w-full justify-center align-center text-sm">
        <div class="relative p-4 m-auto w-1/1 max-w-lg">
            <div class="relative p-4 bg-white rounded-lg shadow">
                <button type="button" data-modal-toggle="modal-trial-download"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                    <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="" />
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="py-6 px-6">
                    <h3 class="mb-4 text-center text-xl font-bold text-brand-color">
                        Congratulations 🎉
                    </h3>
                    <p class="text-center mb-4">Select the appropriate version for your computer. The app will ask for
                        the code below to start using Classer.</p>
                    <h2 class="text-2xl text-center font-bold text-brand-color"><?php echo $trialCode; ?></h2>
                    <div class="flex justify-center wrap gap-4 mt-4">
                        <button
                            class="btn font-semibold text-white justify-center py-3 px-5 text-base text-center rounded-full"
                            onclick="downloadFile('<?php echo $trialDownloadUrl; ?>', 'Classer.zip');">
                            <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24">
                                <path
                                    d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z" />
                            </svg>
                            Intel Chip
                        </button>
                        <button
                            class="btn font-semibold text-white justify-center py-3 px-5 text-base text-center rounded-full"
                            onclick="downloadFile('<?php echo $trialDownloadUrl; ?>', 'Classer.zip');">
                            <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24">
                                <path
                                    d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z" />
                            </svg>
                            Apple Chip
                        </button>
                    </div>
                    <!-- <button class="btn font-semibold text-white justify-center items-center items-center py-3 px-5 text-base font-medium text-center text-gray-900 rounded-full" onclick="downloadFile('<?php echo $trialDownloadUrl; ?>', 'Classer.zip');">
            <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path d="M0 12v-8.646l10-1.355v10.001h-10zm11 0h13v-12l-13 1.807v10.193zm-1 1h-10v7.646l10 1.355v-9.001zm1 0v9.194l13 1.806v-11h-13z" />
            </svg>
            Windows
          </button>
          <button class="btn font-semibold text-white justify-center items-center items-center py-3 px-5 text-base font-medium text-center text-gray-900 rounded-full" onclick="downloadFile('<?php echo $trialDownloadUrl; ?>', 'Classer.zip');">
            <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
              <path d="M20.581 19.049c-.55-.446-.336-1.431-.907-1.917.553-3.365-.997-6.331-2.845-8.232-1.551-1.595-1.051-3.147-1.051-4.49 0-2.146-.881-4.41-3.55-4.41-2.853 0-3.635 2.38-3.663 3.738-.068 3.262.659 4.11-1.25 6.484-2.246 2.793-2.577 5.579-2.07 7.057-.237.276-.557.582-1.155.835-1.652.72-.441 1.925-.898 2.78-.13.243-.192.497-.192.74 0 .75.596 1.399 1.679 1.302 1.461-.13 2.809.905 3.681.905.77 0 1.402-.438 1.696-1.041 1.377-.339 3.077-.296 4.453.059.247.691.917 1.141 1.662 1.141 1.631 0 1.945-1.849 3.816-2.475.674-.225 1.013-.879 1.013-1.488 0-.39-.139-.761-.419-.988zm-9.147-10.465c-.319 0-.583-.258-1-.568-.528-.392-1.065-.618-1.059-1.03 0-.283.379-.37.869-.681.526-.333.731-.671 1.249-.671.53 0 .69.268 1.41.579.708.307 1.201.427 1.201.773 0 .355-.741.609-1.158.868-.613.378-.928.73-1.512.73zm1.665-5.215c.882.141.981 1.691.559 2.454l-.355-.145c.184-.543.181-1.437-.435-1.494-.391-.036-.643.48-.697.922-.153-.064-.32-.11-.523-.127.062-.923.658-1.737 1.451-1.61zm-3.403.331c.676-.168 1.075.618 1.078 1.435l-.31.19c-.042-.343-.195-.897-.579-.779-.411.128-.344 1.083-.115 1.279l-.306.17c-.42-.707-.419-2.133.232-2.295zm-2.115 19.243c-1.963-.893-2.63-.69-3.005-.69-.777 0-1.031-.579-.739-1.127.248-.465.171-.952.11-1.343-.094-.599-.111-.794.478-1.052.815-.346 1.177-.791 1.447-1.124.758-.937 1.523.537 2.15 1.85.407.851 1.208 1.282 1.455 2.225.227.871-.71 1.801-1.896 1.261zm6.987-1.874c-1.384.673-3.147.982-4.466.299-.195-.563-.507-.927-.843-1.293.539-.142.939-.814.46-1.489-.511-.721-1.555-1.224-2.61-2.04-.987-.763-1.299-2.644.045-4.746-.655 1.862-.272 3.578.057 4.069.068-.988.146-2.638 1.496-4.615.681-.998.691-2.316.706-3.14l.62.424c.456.337.838.708 1.386.708.81 0 1.258-.466 1.882-.853.244-.15.613-.302.923-.513.52 2.476 2.674 5.454 2.795 7.15.501-1.032-.142-3.514-.142-3.514.842 1.285.909 2.356.946 3.67.589.241 1.221.869 1.279 1.696l-.245-.028c-.126-.919-2.607-2.269-2.83-.539-1.19.181-.757 2.066-.997 3.288-.11.559-.314 1.001-.462 1.466zm4.846-.041c-.985.38-1.65 1.187-2.107 1.688-.88.966-2.044.503-2.168-.401-.131-.966.36-1.493.572-2.574.193-.987-.023-2.506.431-2.668.295 1.753 2.066 1.016 2.47.538.657 0 .712.222.859.837.092.385.219.709.578 1.09.418.447.29 1.133-.635 1.49zm-8-13.006c-.651 0-1.138-.433-1.534-.769-.203-.171.05-.487.253-.315.387.328.777.675 1.281.675.607 0 1.142-.519 1.867-.805.247-.097.388.285.143.382-.704.277-1.269.832-2.01.832z" />
            </svg>
            Linux
          </button> -->
                </div>
            </div>
        </div>
    </article>
    <!-- / Download modal -->
</body>

<script>
    const imagesDirectory = "https://classermedia.com/assets/images";
    //const imagesDirectory = "http://localhost/assets/images";
</script>


<script>
    const tutorialsItems = [{
        label: 'Highlights and exporting',
        url: 'https://www.youtube.com/watch?v=BKq31l-p6C4',
        thumbnail: `${imagesDirectory}/tutorials/highlights@2x-2.png`,
        cls: ''
    }, {
        label: 'Importing',
        url: 'https://www.youtube.com/watch?v=pl_H80jAtoE',
        thumbnail: `${imagesDirectory}/tutorials/importing@2x-2.png`,
        cls: ''
    }, {
        label: 'Create and search tags',
        url: 'https://www.youtube.com/watch?v=jPNaHiBkl0s',
        thumbnail: `${imagesDirectory}/tutorials/search-a-tag@2x-2.png`,
        cls: ''
    }];

    /**
     * Build tutorial items
     * @param {Array} items
     */
    const buildTutorialItems = (items) => {
        return items.map(item => {
            const isLastItem = items.indexOf(item) === items.length - 1;
            return `
              <div class="relative w-full ${item.cls} hover:opacity-75 transition-opacity duration-300 ease-in-out">
                <a href="${item.url}" target="_blank" class="absolute top-0 left-0 w-full h-full">
                </a>
                <img class="w-full" src="${item.thumbnail}" alt="" />
                <p class="mt-4 text-center mx-auto w-full md:max-w-xs text-xl md:mt-3">${item.label}</p>
              </div>
          `
        }).join('');
    }

    window.addEventListener('load', () => {
        const tutorialsElm = document.querySelector('#tutorials #tutorial-items');
        tutorialsElm.innerHTML = buildTutorialItems(tutorialsItems);
    });
</script>

<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

<script>
    const checkScroll = () => {
        var nav = document.getElementById('nav');
        if (window.pageYOffset > 0) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }

    window.addEventListener('load', checkScroll);
    window.addEventListener('scroll', checkScroll);
</script>

<!-- Hero -->
<script>
    let slides = Array.from(document.querySelectorAll('#slides > div'));
    let indicators = Array.from(document.querySelectorAll('#indicators > button'));
    let currentSlide = 0;
    let slideInterval;

    function goToSlide(slide) {
        slides[currentSlide].classList.remove('opacity-100');
        indicators[currentSlide].classList.remove('bg-brand-color');
        currentSlide = slide;
        slides[currentSlide].classList.add('opacity-100');
        indicators[currentSlide].classList.add('bg-brand-color');
    }

    function nextSlide() {
        let newSlide = currentSlide + 1;
        if (newSlide === slides.length) newSlide = 0;
        goToSlide(newSlide);
    }

    indicators.forEach((indicator, i) => {
        indicator.addEventListener('click', () => {
            clearInterval(slideInterval);
            goToSlide(i);
            slideInterval = setInterval(nextSlide, 10000);
        });
    });

    slides.forEach(slide => {
        slide.addEventListener('transitionend', function() {
            console.log('Transition ended');
            // Your code here
        });
    });

    goToSlide(0);
    // slideInterval = setInterval(nextSlide, 10000);
</script>

<script>
    function isInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function checkArticlesInView() {
        var elements = document.querySelectorAll('#features article');
        elements.forEach(element => {
            if (isInViewport(element)) {
                element.classList.remove('scale-75');
                element.classList.add('scale-100');
            }
        });
    }

    // Usage: @Deprecated
    // window.addEventListener('load', checkArticlesInView);
    // window.addEventListener('scroll', checkArticlesInView);
</script>

<script>
    function onSubmit(token) {
        if (document.getElementById("trial-form").checkValidity() == false) {
            return;
        }

        document.getElementById("trial-form").submit();
    }

    function downloadFile(fileUrl, fileName) {
        var link = document.createElement('a');
        link.href = fileUrl;
        link.download = fileName;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<script>
    // select class toggle model button and add event listener
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("[data-modal-toggle]").forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                const target = button.dataset.modalToggle;
                const modal = document.querySelector(`[data-modal="${target}"]`);
                modal.classList.toggle("hidden");
                modal.classList.toggle("flex");
            });
        });

        document.getElementById("faqs").innerHTML = faqData
            .map(renderFAQItem)
            .join("");

        document.getElementById("features")
            .getElementsByTagName("div")[0]
            .innerHTML = featuresData
            .map(renderArticle)
            .join("");
    });

    // on document load, check for success url param and show modal
    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const modal = urlParams.get("modal");
        let modalElement = null;

        if (modal === 'trial-registration-success') {
            modalElement = document.querySelector(
                `[data-modal="modal-toggle-registering"]`
            );
        }

        if (modal === 'trial-download') {
            modalElement = document.querySelector(
                `[data-modal="modal-trial-download"]`
            );
        }

        modalElement && modalElement.classList.toggle("hidden");
        modalElement && modalElement.classList.toggle("flex");
    });


    document.body.addEventListener("showRegisterSuccessModel", function(e) {
        alert("myEvent was triggered!");
    })
</script>

<script>
    const featuresData = [{
            // title: "Don't lose control of your recordings",
            title: "Discover your moments in full detail",
            listItems: [{
                    imgSrc: `${imagesDirectory}/jam-icons/icons/pictures.svg`,
                    text: "Add all the videos you want",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/camera-f.svg`,
                    text: "Highlight your favourite moments",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/info.svg`,
                    text: "View all your metadata",
                },
            ],
            imgSrc: `${imagesDirectory}/features/feature-1.png`,
            imgAlt: "image description",
        },
        {
            title: "Get insights through your telemetry",
            listItems: [{
                    imgSrc: `${imagesDirectory}/jam-icons/icons/folder-open.svg`,
                    text: "Learn about your speed",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/hashtag.svg`,
                    text: "Check where you have been",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/pin-f.svg`,
                    text: "Check your best times",
                }
            ],
            imgSrc: `${imagesDirectory}/features/feature-2.png`,
            imgAlt: "image description",
        },
        {
            title: "Organise and find your memories",
            listItems: [{
                    imgSrc: `${imagesDirectory}/jam-icons/icons/scissors.svg`,
                    text: "Search by tags",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/download.svg`,
                    text: "Pin videos for a quick and simple navigation",
                },
                {
                    imgSrc: `${imagesDirectory}/jam-icons/icons/download.svg`,
                    text: "Make it a favourite",
                },
            ],
            imgSrc: `${imagesDirectory}/features/feature-3.png`,
            imgAlt: "image description",
        },
    ];

    const faqData = [{
            question: "Is it for mobile?",
            answer: "We are currently focusing on desktop, but with future plans to make it work for mobile too.",
        },
        {
            question: "Can I cut and trim my videos?",
            answer: "Yes, classer allows you to cut and trim your videos to easily share them.",
        },
        {
            question: "Is this a cloud service?",
            answer: "No, Classer is a desktop application that provides a simple solution to organizing and view all your videos.",
        },
        {
            question: "Does Classer use my directory from my folder file?",
            answer: "Yes, Classer leverages the existing structure of your file folder, enabling quicker access to what you're seeking.",
        },
        {
            question: "Does it work with all action cameras?",
            answer: "Yes and all video file formats, including .mp4, .mov, .avi",
        },
        {
            question: "I would like to contact the team, how do I do it?",
            answer: "Happy to chat! Please contact us at info@classermedia.com",
        },
        {
            question: "I already have a folder structure, would Classer follow it?",
            answer: "Yes, Classer identify your folder structure and add them in.",
        },
        {
            question: "How to turn on my GPS on my GoPro?",
            answer: "From the main screen from GoPro, swipe down (HERO11/10/9 white, swipe left after swiping down) and tap [Preferences]. For HERO11 Black, scroll to [GPS] and turn GPS [On]. For HERO10/9 Black, scroll to [Regional], tap [GPS] and turn GPS [On].",
        },
    ];

    const renderArticle = (data, i) => {
        const reversed = i % 2 === 0;
        return `
        <article class="md:flex md:flex-nowrap m-auto ${i !== 0 && "mt-12 xl:mt-16"} ${!reversed && "flex-row-reverse"}">
          <div class="place-self-center">
            <div class="place-self-center m-auto">
              <h3 class="leading-tight my-6 lg:mt-0 text-brand-color text-4xl md:text-2xl lg:text-5xl font-semibold">
                ${data.title}
              </h3>
              ${data.listItems
                .map(
                  (item) => `
                <p class="mb-4">
                  <img class="inline text-brand-color" src="${item.imgSrc}" alt="" />
                  ${item.text}
                </p>
              `
                )
                .join("")}
            </div>
          </div>
          <div class="w-full md:w-11/12 scale-110">
            <img src="${data.imgSrc}"
              alt="${data.imgAlt}" />
          </div>
        </article>
      `;
    };

    const renderFAQItem = (faqItem) => {
        return `
        <div >
          <h3 class="flex items-center mb-4 mb-2 mt-6 text-brand-color text-xl font-bold">
            ${faqItem.question}
          </h3>
          <p class="md:max-w-xs">
            ${faqItem.answer}
          </p>
        </div>
      `;
    };
</script>

</div>

</html>
