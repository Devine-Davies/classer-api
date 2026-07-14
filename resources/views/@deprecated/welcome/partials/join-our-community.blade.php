<style>
    .community:before,
    .community::after {
        content: "";
        @apply absolute left-0 top-0 z-0 hidden h-full w-[60%];
        background-image: url(https://classermedia.com/assets/images/welcome/background-picture.png);
        background-repeat: no-repeat;
        background-size: initial;
        background-position: -1465px -90px;
    }

    .community:before {
        left: -85px;
    }

    .community:after {
        left: calc(100% + 85px);
        transform: rotateY(180deg) translateX(100%);
    }

    @media (min-width: 768px) {

        .community:before,
        .community::after {
            display: block;
        }
    }

    @media (min-width: 1024px) {

        .community:after,
        .community:before {
            background-position: -1170px -0px;
        }
    }

    @media (min-width: 1280px) {

        .community:before,
        .community:after {
            background-position: -1125px -0px;
        }
    }

    @media (min-width: 1536px) {

        .community:before,
        .community:after {
            background-position: -1125px -0px;
        }
    }
</style>

<section class="community mt-10 text-center relative overflow-hidden max-w-full px-4" style="overflow: hidden;">
    <header class="mt-16 mb-8 text-center max-w-6xl m-auto">
        <h1 class="text-brand-color font-bold sm:px-16 xl:px-48 text-3xl md:text-4xl lg:text-5xl">Join the adventures?
        </h1>
    </header>

    <p class="mt-2 font-bold text-lg text-gray-500">Wev'e got the perfect spot.</p>

    <div class="mx-auto max-w-3xl lg:max-w-5xl pb-8 md:pb-16 flex flex-wrap center justify-center relative z-10 gap-y-4">
        <div class="m-auto max-w-xl">
            <p class="text-base text-center">As we continue this journey together, we’re grateful to have built a vibrant
                community of adventurers, thrill-seekers, and storytellers who love capturing and sharing life’s best
                moments.</p>

            <br />

            <p class="text-base text-center">We’d love for you to be part of it and share your own experiences, get
                inspired by others, and
                enjoy the latest news and fun videos along the way.</p>

            <a aria-label="Community"
                class="btn bg-gold text-brand-color mt-4 inline-block font-semibold text-white py-3 px-8 rounded-full"
                href="https://www.reddit.com/r/ActionCam/" target="_blank">
                Join Here
            </a>
        </div>
    </div>
</section>
