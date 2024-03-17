{{-- Main modal --}}
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
                        <input type="submit" value="Register"
                            class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full" />
                    </div>
                </form>

                <div id="register-success" class="p-6 hidden">
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

{{-- Modal thankyou for registering --}}
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
            <div class="py-6 px-6">
                <h3 class="mb-4 text-center text-xl font-bold text-brand-color">
                    Glad to have you on board 🎉
                </h3>
                <p class="text-center">Check your email for Classer for your download link and access code.</p>
            </div>
        </div>
    </div>
</article>

{{-- Download modal --}}
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
            </div>
        </div>
    </div>
</article>

<script>

    document.addEventListener('htmx:beforeRequest', (evt) => {
        google.recaptcha.execute('6LeT-wwmAAAAAL64va5W33XKEhALIBLnjeDv_FtL', {
            action: 'register'
        }).then(function(token) {
            document.getElementById('register-form').insertAdjacentHTML('beforeend', '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
        });
    });

    document.addEventListener('htmx:afterRequest', (evt) => {
        if (evt.detail.successful != true) {
            return alert("Register error, please try again ");
        }

        document.getElementById("register-success").classList.remove("hidden");
        document.getElementById("register-form").classList.add("hidden");
    });

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
