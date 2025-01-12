<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Login</title>
    <script>
        pageUrl = "{{ url('/') }}";
    </script>

    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
    @vite('resources/views/auth/admin/login/index.css')
    @vite('resources/views/auth/admin/login/index.js')
</head>

<body>
    @include('partials.shared.naviagtion')

    <article tabindex="-1" class="overflow-hidden w-full h-screen flex justify-center items-center"
        style="background-color: rgb(10 64 77); height: calc(100vh - 64px);">
        @include('partials.shared.triangles')

        <div class="relative px-6 py-16 bg-white rounded-lg shadow w-11/12 max-w-2xl">
            <div id="form">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Login
                    </h3>
                </div>

                <form class="space-y-6 m-auto max-w-md" hx-post="{{ url('/') }}/api/auth/admin/login"
                    hx-target="#api-results">@csrf
                    {{-- Hack due to setTimout, we don't show the response --}}
                    <div id="api-results">
                    </div>

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" name="email" id="email" value=""
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            placeholder="yourEmail@example.com" required />
                    </div>

                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="******" required
                                class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400" />

                            <span
                                class="eye-show-password block absolute w-6 h-6 right-2 top-1/2 transform -translate-y-1/2 rounded-full cursor-pointer">
                                <svg class="w-6 h-6 text-gray-400 dark:text-white" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center align-middle gap-4">
                        <div>
                            <div class="loading-spinner hidden"></div>
                        </div>
                        <p class="error-message text-sm font-semibold text-red-500"></p>
                        <input type="submit" value="Login"
                            class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full disabled:opacity-75 disabled:pointer-events-none" />
                    </div>
                </form>
            </div>

            <div id="stats-container" class="flex flex-wrap gap-y-4">

            </div>
        </div>
    </article>
</body>

<script type="text/template" id="stats-template">
                <div hx-get="{{ url('/') }}/api/auth/admin/boom" hx-target="#stats-container" hx-swap="outerHTML"
                    hx-trigger="load" hx-indicator=".loading-spinner" hx-headers='{"Accept": "application/json"}'>
                </div>

    <div class="w-full px-6 sm:w-1/2 xl:w-1/3">
        <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-slate-100">
            <div class="p-3 rounded-full bg-indigo-600 bg-opacity-75">
                <svg class="h-8 w-8 text-white" viewBox="0 0 28 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M18.2 9.08889C18.2 11.5373 16.3196 13.5222 14 13.5222C11.6804 13.5222 9.79999 11.5373 9.79999 9.08889C9.79999 6.64043 11.6804 4.65556 14 4.65556C16.3196 4.65556 18.2 6.64043 18.2 9.08889Z"
                        fill="currentColor"></path>
                    <path
                        d="M25.2 12.0444C25.2 13.6768 23.9464 15 22.4 15C20.8536 15 19.6 13.6768 19.6 12.0444C19.6 10.4121 20.8536 9.08889 22.4 9.08889C23.9464 9.08889 25.2 10.4121 25.2 12.0444Z"
                        fill="currentColor"></path>
                    <path
                        d="M19.6 22.3889C19.6 19.1243 17.0927 16.4778 14 16.4778C10.9072 16.4778 8.39999 19.1243 8.39999 22.3889V26.8222H19.6V22.3889Z"
                        fill="currentColor"></path>
                    <path
                        d="M8.39999 12.0444C8.39999 13.6768 7.14639 15 5.59999 15C4.05359 15 2.79999 13.6768 2.79999 12.0444C2.79999 10.4121 4.05359 9.08889 5.59999 9.08889C7.14639 9.08889 8.39999 10.4121 8.39999 12.0444Z"
                        fill="currentColor"></path>
                    <path
                        d="M22.4 26.8222V22.3889C22.4 20.8312 22.0195 19.3671 21.351 18.0949C21.6863 18.0039 22.0378 17.9556 22.4 17.9556C24.7197 17.9556 26.6 19.9404 26.6 22.3889V26.8222H22.4Z"
                        fill="currentColor"></path>
                    <path
                        d="M6.64896 18.0949C5.98058 19.3671 5.59999 20.8312 5.59999 22.3889V26.8222H1.39999V22.3889C1.39999 19.9404 3.2804 17.9556 5.59999 17.9556C5.96219 17.9556 6.31367 18.0039 6.64896 18.0949Z"
                        fill="currentColor"></path>
                </svg>
            </div>

            <div class="mx-5">
                <h4 class="text-2xl font-semibold text-gray-700">${stat}</h4>
                <div class="text-gray-500">${title}</div>
            </div>
        </div>
    </div>
</script>

</html>

