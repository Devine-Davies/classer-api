{{-- Download --}}
<article tabindex="-1" data-modal="download"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center backdrop-blur-md">
    <div class="relative p-4 m-auto w-1/1 max-w-lg ">
        <div class="relative p-4 bg-white rounded-lg shadow">
            <button type="button" data-modal-close
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                <img src="{{ asset('/assets/images/jam-icons/icons/close.svg') }}" alt="Close icon" />
                <span class="sr-only">Close modal</span>
            </button>
            <div class="py-6 px-6">
                <h3 class="mb-4 text-center text-xl font-bold text-brand-color">
                    Download
                </h3>
                <p class="text-center mb-4">Select the appropriate version for your computer to start downloading Classer.</p>
                <h2 class="text-2xl text-center font-bold text-brand-color"><?php echo $trialCode; ?></h2>
                <div class="flex justify-center wrap gap-4 mt-4">
                    <a href="{!! url('/releases/download?platform=darwin&architecture=x64') !!}"
                        class="btn font-semibold text-white justify-center py-3 px-5 text-base text-center rounded-full">
                        <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path
                                d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z" />
                        </svg>
                        Intel Chip
                    </a>
                    <a href="{!! url('/releases/download?platform=darwin&architecture=arm64') !!}"
                        class="btn font-semibold text-white justify-center py-3 px-5 text-base text-center rounded-full">
                        <svg style="fill:white;position: relative;top: -2px;" class="inline-flex mr-2"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path
                                d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z" />
                        </svg>
                        Apple Chip
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const modal = urlParams.get("modal");
        modal && displayModal(modal);

        document.querySelectorAll("[data-modal-close]").forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                const modals = document.querySelectorAll("[data-modal]");
                modals.forEach((modal) => {
                    modal.classList.add("hidden");
                    modal.classList.remove("flex");
                });
            });
        });

        document.querySelectorAll("[data-modal-open]").forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                const urlParams = new URLSearchParams(button.getAttribute("href"));
                const modal = urlParams.get("modal");
                displayModal(modal);
            });
        });
    });

    const displayModal = (target) => {
        const modal = document.querySelector(`[data-modal="${target}"]`);
        modal && modal.classList.toggle("hidden");
        modal && modal.classList.toggle("flex");
    }
</script>
