<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Login</title>
    <script>
        pageUrl = "{{ url('/') }}";
        adminLoginRedirectUrl = "{{ url('/admin/stats') }}";
    </script>

    @include('partials.shared.meta')
    @vite('resources/views/admin/login/index.css')
    <!-- @vite('resources/views/admin/login/index.js') -->
</head>

<body>
    @include('partials.shared.navigation')

    <article class="overflow-hidden w-full h-screen flex justify-center items-center"
        style="background-color: rgb(10 64 77); height: calc(100vh - 64px);">
        @include('partials.shared.triangles')

        <div class="relative bg-white rounded-lg shadow w-11/12 max-w-5xl p-8">
            <div class="mb-6 m-auto max-w-3xl">
                <x-admin.flash-messages />
            </div>

            <div id="form">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Login
                    </h3>
                </div>

                <form class="space-y-6 m-auto max-w-md" method="POST" action="{{ url('/admin/login') }}">
                    @csrf

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            placeholder="yourEmail@example.com"
                            required
                        />
                    </div>

                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="******"
                                required
                                class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between items-center align-middle gap-4">
                        <input
                            type="submit"
                            value="Login"
                            class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full disabled:opacity-75 disabled:pointer-events-none"
                        />
                    </div>
                </form>
            </div>
        </div>
    </article>
</body>

</html>
