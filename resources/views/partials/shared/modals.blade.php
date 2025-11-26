@php
    $downloads = [
        [
            'label' => 'Windows',
            'sub'   => 'Windows 10 or later',
            'href'  => url('/download?platform=win'),
            'icon'  => 'windows',
            'divider' => true,
        ],
        [
            'label' => 'MacOS (Apple Silicon)',
            'sub'   => 'For M1, M2, M3 chips • macOS 10.14+',
            'href'  => url('/download?platform=mac&architecture=arm64'),
            'icon'  => 'apple',
            'divider' => false,
        ],
        [
            'label' => 'MacOS (Intel)',
            'sub'   => 'For Intel-based Macs • macOS 10.14+',
            'href'  => url('/download?platform=mac&architecture=x64'),
            'icon'  => 'apple',
            'divider' => false,
        ],
    ];
@endphp


{{-- Download --}}
{{-- href="?modal=download" data-modal-open --}}

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

                {{-- DOWNLOAD OPTIONS --}}
                <div class="space-y-6">
                    @foreach ($downloads as $d)
                        <a
                            target="_blank"
                            href="{{ $d['href'] }}"
                            class="flex items-center gap-5 group cursor-pointer"
                        >
                            <span class="text-blue-500 fill-current hover:text-blue-700">
                                @if ($d['icon'] === 'apple')
                                    @icon(apple)

                                @elseif ($d['icon'] === 'windows')
                                    @icon(windows)
                                @endif
                            </span>

                            <div>
                                <p class="text-xl text-sky-500 font-bold group-hover:text-sky-700">
                                    {{ $d['label'] }}
                                </p>
                                <p class="text-sm text-slate-600">{{ $d['sub'] }}</p>
                            </div>
                        </a>

                        @if (isset($d['divider']) && $d['divider'])
                            <hr class="my-6 border-gray-300">
                        @endif
                    @endforeach
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
