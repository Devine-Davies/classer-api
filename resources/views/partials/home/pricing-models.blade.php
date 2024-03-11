@php
    $tickIcon = '<svg style="color: black" xmlns="http://www.w3.org/2000/svg" viewBox="-5 -7 24 24" width="28" fill="currentColor"><path d="M5.486 9.73a.997.997 0 0 1-.707-.292L.537 5.195A1 1 0 1 1 1.95 3.78l3.535 3.535L11.85.952a1 1 0 0 1 1.415 1.414L6.193 9.438a.997.997 0 0 1-.707.292z"></path></svg>';
@endphp

<section>
    <div class="mx-auto">
        <h3 class="text-3xl md:text-4xl lg:text-5xl font-bold text-center text-brand-color">
            Discover our plan today
        </h3>
        <p class="text-center mx-auto mt-3 lg:text-xl">
            We are currently opening Classer for free for all users to enjoy all our new features.
        </p>
    </div>

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-center flex-wrap md:flex-nowrap my-8 md:my-10 m-auto gap-8 md:gap-8">
            <div class="w-full text-center rounded-md max-w-md border">
                <div class="bg-gray-100 p-4 w-full text-center relative">
                    <h2 class="text-2xl my-4 tracking-widest uppercase">Beta Version</h2>
                    <p class="my-4 max-w-xs m-auto">Become a team beta tester for all our new features</p>
                </div>

                <div>
                    <h3 class="uppercase text-4xl py-6 font-bold">Free</h3>
                    <ul class="m-auto list-none flex flex-col gap-y-4 w-4/5">
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Unlimited</span> number of videos</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Organising, Searching, Tagging</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Maps, Speed, Telemetry</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Clipping, Trimming, Merging</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">And much more</p>
                        </li>
                    </ul>

                    <div class="py-12">
                        <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                            class="btn inline font-semibold text-white py-3 px-8 rounded-full">
                            Get Classer
                        </a>
                        <p class="mt-5 text-sm">Available for <span class="font-semibold" >Mac</span> and <span class="font-semibold" >Windows<span></p>
                    </div>
                </div>
            </div>

            <div class="w-full text-center rounded-md max-w-md border relative">
                <div class="bg-badge p-4 w-full text-center">
                    <h2 class="text-2xl my-4 tracking-widest uppercase">Pro Modal</h2>
                    <p class="my-4 max-w-sm m-auto">Ideal for keeping your moments on the cloud and share them with friends and family</p>
                </div>

                <div class="relative">
                    <div class="absolute top-0 left-0 w-full h-full backdrop-blur-sm"></div>
                    <h3 class="uppercase text-4xl py-6 font-bold blur-md ">£0.00</h3>

                    <ul class="m-auto list-none flex flex-col gap-y-4 w-4/5">
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Lorem ipsum dolor sit amet</span></p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Lorem ipsum dolor sit</span> number</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Lorem ipsum</span> number</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Lorem ipsum dolor sit amet</span> number</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Lorem ipsum dolor sit amet</span> number</p>
                        </li>
                    </ul>

                    <div class="pt-12 pb-8">
                        <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                            class="btn inline font-semibold text-white py-3 px-8 rounded-full">
                            Get Classer
                        </a>
                    </div>
                </div>

                <div class="text-white flex p-4 gap-6 rounded-b-md text-left" style="background-color: #016db9;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="28" fill="currentColor"><path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm0 2C4.477 20 0 15.523 0 10S4.477 0 10 0s10 4.477 10 10-4.477 10-10 10z"></path><path d="M10 18c.448 0 1.119-.568 1.747-1.823C12.532 14.607 13 12.392 13 10c0-2.392-.468-4.607-1.253-6.177C11.119 2.568 10.447 2 10 2c-.448 0-1.119.568-1.747 1.823C7.468 5.393 7 7.608 7 10c0 2.392.468 4.607 1.253 6.177C8.881 17.432 9.553 18 10 18zm0 2c-2.761 0-5-4.477-5-10S7.239 0 10 0s5 4.477 5 10-2.239 10-5 10z"></path><path d="M2 12h16v2H2v-2zm0-6h16v2H2V6z"></path></svg>
                    <p>New features coming soon including cloud services</p>
                </div>
            </div>
        </div>
    </div>
</section>
