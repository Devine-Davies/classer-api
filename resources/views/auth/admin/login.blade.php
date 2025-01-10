<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Register</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
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
        </div>
    </article>
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        grecaptcha.ready(function() {
            grecaptcha.execute('6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq', {
                action: 'submit'
            }).then(function(token) {
                document.querySelector('#form form').insertAdjacentHTML('beforeend',
                    '<div class="hidden" ><input type="hidden" name="grc" value="' + token +
                    '"></div>');
            });
        });
    });

    document.addEventListener('htmx:beforeRequest', (evt) => {
        document.querySelector(".loading-spinner").classList.remove("hidden");
        document.querySelector(".error-message").classList.add("hidden");
        document.querySelector("input[type=submit]").classList.add("pointer-events-none");
    });

    document.addEventListener('htmx:afterRequest', (evt) => {
        const res = JSON.parse(evt.detail.xhr.response);
        setTimeout(() => {
            document.querySelector(".loading-spinner").classList.add("hidden");
            if (evt.detail.successful != true) {
                document.querySelector("input[type=submit]").classList.remove("pointer-events-none");

                const errorElm = document.querySelector(".error-message");
                errorElm.innerHTML = [500, 401].includes(evt.detail.xhr.status) ? res.message :
                    "Something went wrong, please try again.";
                errorElm.classList.remove("hidden");
                return;
            } else {
                const token = evt.detail.xhr.getResponseHeader('x-token');
                requestStats(token);
            }
        }, 500);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const eyeButtons = document.querySelectorAll('.eye-show-password');
        eyeButtons.forEach((eyeButton) => {
            eyeButton.addEventListener('click', (e) => {
                const input = eyeButton.previousElementSibling;
                const type = input.getAttribute('type') === 'password' ? 'text' :
                    'password';
                input.setAttribute('type', type);
            });
        });
    });

    /**
     * Request stats
     * @param {string} token 
     */
    const requestStats = (token) => {
        fetch("{{ url('/') }}/api/admin/stats", {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token,
            },
        }).then(response => {
            response.json().then(data => {
                Object.entries(data.data).forEach(([key, value]) => {
                    console.log(key, value);
                });
            });
        })
    }
</script>