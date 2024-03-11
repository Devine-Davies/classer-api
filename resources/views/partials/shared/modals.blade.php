<!-- Main modal -->
<article tabindex="-1" data-modal="modal-toggle"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center">
    <div class="relative p-4 m-auto w-1/1 max-w-2xl">
        <!-- Modal content -->
        <div class="relative p-4 bg-white rounded-lg shadow">
            <button type="button"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white"
                data-modal-toggle="modal-toggle">
                <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
                <span class="sr-only">Close modal</span>
            </button>
            <div class="py-6 px-6">
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
                            class="g-recaptcha btn inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-gray-900 rounded-full" />
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
                <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
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
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center text-sm">
    <div class="relative p-4 m-auto w-1/1 max-w-lg">
        <div class="relative p-4 bg-white rounded-lg shadow">
            <button type="button" data-modal-toggle="modal-trial-download"
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
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
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path
                                d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z" />
                        </svg>
                        Intel Chip
                    </button>
                    <button
                        class="btn font-semibold text-white justify-center py-3 px-5 text-base text-center rounded-full"
                        onclick="downloadFile('<?php echo $trialDownloadUrl; ?>', 'Classer.zip');">
                        <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
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
    });
</script>
