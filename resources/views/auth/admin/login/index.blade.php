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

        <div class="relative bg-white rounded-lg shadow w-11/12 max-w-5xl p-8">
            <div id="form">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Login
                    </h3>
                </div>

                <form class="space-y-6 m-auto max-w-md" hx-post="{{ url('/') }}/api/auth/admin/login" hx-swap="none">
                    @csrf

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

            <!-- Alpine.js must be included -->
            <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

            <div x-data="{ tab: 'stats' }" class="space-y-4 hidden overflow-y-auto" style="max-height: calc(100vh - 164px);" >
                <div class="flex space-x-4 border-b">
                    <button @click="tab = 'stats'" :class="{ 'border-b-2 font-bold': tab === 'stats' }"
                        class="pb-2">Stats</button>
                    <button @click="tab = 'logs'" :class="{ 'border-b-2 font-bold': tab === 'logs' }"
                        class="pb-2">Logs</button>
                </div>

                <div x-show="tab === 'stats'">
                    <div id="stats-container" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-2">
                    </div>
                </div>

                <div x-show="tab === 'logs'">
                    <!-- Somewhere in your HTML view -->
                    <table class="w-full table-auto text-left border rounded">
                        <thead class="bg-gray-100 text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-center">Type</th>
                                <th class="px-4 py-2">Timestamp</th>
                                <th class="px-4 py-2">Context</th>
                                <th class="px-4 py-2">Message</th>
                            </tr>
                        </thead>
                        <tbody id="logs-container">
                            <!-- Rows rendered by JS -->
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </article>
</body>

<script type="text/template" id="stats-template">
    <div class="w-full stats-card">
        <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-slate-100 h-full">
            <div class="p-3 rounded-full bg-opacity-75 ${color}"></div>
            <div class="mx-5">
                <h4 class="text-2xl font-semibold text-gray-700">${stat}</h4>
                <div class="text-gray-500 tracking-tight text-base">${title}</div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="logs-template">
    <tr class="border-b hover:bg-gray-50">
        <td class="px-4 py-2 text-center">${icon}</td>
        <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">${timestamp}</td>
        <td class="px-4 py-2 text-sm font-semibold text-gray-800">${context}</td>
        <td class="px-4 py-2 text-sm text-gray-600">
            <p class="whitespace-pre-wrap">${message}</p>
        </td>
    </tr>
</script>

</html>
