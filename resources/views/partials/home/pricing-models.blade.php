@php
    $tickIcon = '<svg style="color: black" xmlns="http://www.w3.org/2000/svg" viewBox="-5 -7 24 24" width="28" fill="currentColor"><path d="M5.486 9.73a.997.997 0 0 1-.707-.292L.537 5.195A1 1 0 1 1 1.95 3.78l3.535 3.535L11.85.952a1 1 0 0 1 1.415 1.414L6.193 9.438a.997.997 0 0 1-.707.292z"></path></svg>';
@endphp

<section>
    <div class="mx-auto">
        <h3 class="text-4xl font-bold text-center text-brand-color">
            Discover our plan today
        </h3>
        <p class="text-xl text-center mx-auto mt-3 max-w-md md:max-w-3xl">
            We are currently opening Classer for free for all users to enjoy all our new features.
        </p>
    </div>

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-center flex-wrap md:flex-nowrap gap-8 mx-8 my-14 m-auto md:gap-14">
            <div class="w-full text-center rounded-md max-w-md border">
                <div class="bg-gray-100 p-4 w-full text-center relative">
                    <h2 class="text-2xl font-bold my-4 tracking-widest uppercase">Bata Version</h2>
                    <p class="font-bold my-4 max-w-xs m-auto">Become a team beta tester for all our new features</p>
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
                            <p><span class="font-semibold">Organizing, Telemetry, GPS</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">Clipping, Trimming, Merging</p>
                        </li>
                        <li class="flex items-center">
                            {!! $tickIcon !!}
                            <p><span class="font-semibold">And mouch more</p>
                        </li>
                    </ul>

                    <div class="py-12">
                        <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                            class="btn inline font-semibold text-white py-3 px-8 rounded-full">
                            Get Classer
                        </a>
                        <p class="mt-6" >Available for Mac and Windows</p>
                    </div>
                </div>
            </div>

            <div class="w-full text-center rounded-md max-w-md border relative">
                <div class="bg-gray-100 p-4 w-full text-center">
                    <h2 class="text-2xl font-bold my-4 tracking-widest uppercase">Pro Modal</h2>
                    <p class="font-bold my-4 max-w-sm m-auto">Ideal for keeping your moments on the cloud and share them with friends and family</p>
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
                    </ul>

                    <div class="pt-12 pb-8">
                        <a aria-label="Download Classer" href="#" data-modal-toggle="modal-toggle"
                            class="btn inline font-semibold text-white py-3 px-8 rounded-full">
                            Get Classer
                        </a>
                    </div>
                </div>

                <div class="bg-blue-500 text-white flex p-6 gap-6 rounded-b-md text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-3 -3 24 24" width="28" fill="currentColor"><path d="M2.079 15.921c.815.816 5.102-.95 8.997-4.845 3.895-3.895 5.66-8.182 4.845-8.997-.815-.816-5.102.95-8.997 4.845-3.895 3.895-5.66 8.182-4.845 8.997zM.694 17.306c-1.91-1.912.258-7.18 4.845-11.767S15.394-1.217 17.306.694c1.91 1.912-.258 7.18-4.845 11.767S2.606 19.217.694 17.306z"></path><path d="M6.924 11.076c3.895 3.895 8.182 5.66 8.997 4.845.816-.815-.95-5.102-4.845-8.997-3.895-3.895-8.182-5.66-8.997-4.845-.816.815.95 5.102 4.845 8.997zm-1.385 1.385C.952 7.874-1.217 2.606.694.694c1.912-1.91 7.18.258 11.767 4.845s6.756 9.855 4.845 11.767c-1.912 1.91-7.18-.258-11.767-4.845z"></path><circle cx="9" cy="9" r="2"></circle></svg>
                    <p class="font-bold" >New features coming soon including cloud services</p>
                </div>
            </div>
        </div>
    </div>
</section>
