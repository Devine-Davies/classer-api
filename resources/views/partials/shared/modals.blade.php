{{-- Download --}}
<article tabindex="-1" data-modal="download"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 z-50 h-full w-full justify-center align-center backdrop-blur-md">
    <div class="relative p-4 m-auto w-1/1 max-w-lg ">
        <div class="relative p-4 bg-white rounded-lg shadow">
            <button type="button" data-modal-close
                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-off-white-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-off-white-800 dark:hover:text-white">
                @icon(close)
                <span class="sr-only">Close modal</span>
            </button>
            <div class="py-6 px-6">
                <h3 class="mb-0 text-center text-xl font-bold text-brand-color">
                    Download
                </h3>
                <p class="mb-6 w-full text-center">Select the appropriate version for your computer to start downloading
                    Classer.</p>

                <div class="flex gap-4">
                    <div>
                        <svg fill="#3b82f6" xmlns="http://www.w3.org/2000/svg" class="text-blue-500"
                            style="width: 24px; height: 24px;" viewBox="0 -3.552713678800501e-15 820 950">
                            <path
                                d="M404.345 229.846c52.467 0 98.494-20.488 138.08-61.465s59.38-88.626 59.38-142.947c0-5.966-.472-14.444-1.414-25.434-6.912.942-12.096 1.727-15.552 2.355-48.383 6.908-90.954 30.615-127.713 71.12-36.758 40.506-55.137 83.838-55.137 129.996 0 5.337.785 14.13 2.356 26.375zM592.379 950c37.387 0 78.701-25.59 123.943-76.772S796.122 761.915 820 692.836c-88.912-45.844-133.368-111.626-133.368-197.348 0-71.591 35.973-132.82 107.92-183.688-49.954-62.486-115.931-93.729-197.931-93.729-34.56 0-66.134 5.181-94.724 15.543l-17.908 6.594-24.035 9.42c-15.709 5.966-30.004 8.95-42.885 8.95-10.054 0-23.25-3.455-39.586-10.363l-18.38-7.536-17.436-7.065c-25.449-10.676-52.782-16.014-82-16.014-78.23 0-141.065 26.376-188.506 79.128C23.72 349.479 0 419.03 0 505.379c0 121.517 38.015 233.772 114.046 336.763C166.828 914.047 215.054 950 258.724 950c18.537 0 36.916-3.611 55.138-10.833l23.092-9.42 18.38-6.594c25.762-9.106 49.482-13.659 71.16-13.659 22.935 0 49.326 5.81 79.173 17.427l14.609 5.652C550.75 944.191 574.786 950 592.379 950z" />
                        </svg>
                    </div>
                    <div>
                        <a href="{!! url('/download?platform=mac&architecture=x64') !!}" class="text-blue-500 font-bold text-lg" target="_blank">
                            MacOS (Intel)
                        </a>
                        or
                        <a href="{!! url('/download?platform=mac&architecture=arm64') !!}" class="text-blue-500 font-bold text-lg" target="_blank">
                            MacOS (M1, M2, M3, M4)
                        </a>
                        <p>macOS Mojave 10.14 and later</p>
                    </div>
                </div>

                <!-- Create a light grey horizontal line -->
                <hr class="my-6 border-gray-300">

                <div class="flex gap-4">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision"
                            text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd"
                            fill="#3b82f6"
                            clip-rule="evenodd" viewBox="0 0 640 640"
                            class="" style="width: 24px; height: 24px;">
                            <path
                                d="M.2 298.669L0 90.615l256.007-34.76v242.814H.201zM298.658 49.654L639.905-.012v298.681H298.657V49.654zM640 341.331l-.071 298.681L298.669 592V341.332h341.33zM255.983 586.543L.189 551.463v-210.18h255.794v245.26z" />
                        </svg>
                    </div>
                    <div>
                        <a href="{!! url('/download?platform=win') !!}" class="text-blue-500 font-bold text-lg" target="_blank">
                            Windows Store
                        </a>
                        <p>Windows 10 and later</p>
                    </div>
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
