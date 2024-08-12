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
                <p class="mb-4">Select the appropriate version for your computer to start downloading
                    Classer.</p>
                <h2 class="text-2xl text-center font-bold text-brand-color"><?php echo $trialCode; ?></h2>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <a href="{!! url('/releases/download?platform=darwin&architecture=x64') !!}">
                        <img width="100%" height="50" src="{{ asset('assets/images/mac-app-store-badge.svg') }}"
                            alt="MAS icon" />
                    </a>

                    <a href="{!! url('/releases/download?platform=darwin&architecture=x64') !!}">
                        <img width="100%" height="50" src="{{ asset('assets/images/ms-store-badge.svg') }}"
                            alt="MS icon" />
                    </a>
                </div>
                <p class="mt-8">Like a more direct download? Use the links below and download Classer directly apple</p>
                <div class="flex flex-col gap-1 mt-2">
                    <a class="mr-4 text-blue-500" href="{!! url('/releases/download?platform=darwin&architecture=x64') !!}" >- Apple Chip</a>
                    <a class="mr-4 text-blue-500" href="{!! url('/releases/download?platform=darwin&architecture=x64') !!}" >- Apple Intel</a>
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
