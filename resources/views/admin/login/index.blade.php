<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Login</title>

    @include('partials.meta')
    @vite('resources/views/admin/login/index.css')
</head>

<body>
    @include('partials.navigation')

    <article class="overflow-hidden w-full h-screen flex justify-center items-center"
        style="background-color: rgb(10 64 77); height: calc(100vh - 64px);">
        @include('partials.triangles')

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
                    <input type="hidden" name="grc" value="">

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white-600 dark:border-gray-500 dark:placeholder-gray-400"
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
                                class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            />

                            <button
                                type="button"
                                class="eye-show-password block absolute w-6 h-6 right-2 top-1/2 transform -translate-y-1/2 rounded-full cursor-pointer"
                                aria-label="Toggle password visibility"
                            >
                                <span class="text-gray-400 dark:text-white" aria-hidden="true" data-password-icon="hidden">@icon('eye')</span>
                                <span class="hidden text-gray-400 dark:text-white" aria-hidden="true" data-password-icon="visible">@icon('eye-off')</span>
                            </button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const eyeButtons = document.querySelectorAll('.eye-show-password');

        eyeButtons.forEach((eyeButton) => {
            eyeButton.addEventListener('click', () => {
                const input = eyeButton.previousElementSibling;

                if (!input) {
                    return;
                }

                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);

                const hiddenIcon = eyeButton.querySelector('[data-password-icon="hidden"]');
                const visibleIcon = eyeButton.querySelector('[data-password-icon="visible"]');

                hiddenIcon?.classList.toggle('hidden', type !== 'password');
                visibleIcon?.classList.toggle('hidden', type === 'password');
            });
        });
    });
</script>
