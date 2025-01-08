@php
    $triangles = ['al fg sm', '', 'al fg md', 'lg', 'al fg'];
@endphp

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

        <div class="absolute overflow-hidden top-0 left-0 w-full h-full bg-gradient-to-r from-brand-color to-brand-color z-0"
            style="filter: blur(30px);">
            <div class="bg-mountains">
                <div class="mountains">
                    @foreach ($triangles as $triangle)
                        <div class="triangle {{ $triangle }}"></div>
                    @endforeach
                </div>

                <div class="mountains">
                    @foreach ($triangles as $triangle)
                        <div class="triangle {{ $triangle }}"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="relative px-6 py-16 bg-white rounded-lg shadow w-11/12 max-w-2xl">
            <div id="form">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Make it secure 🔒
                    </h3>
                    <p>Almost there! Assign a password for <span class="font-semibold">{{ $userEmail }}</span> and
                        start using Classer.</p>
                </div>

                <form class="space-y-6 m-auto max-w-md" hx-post="{{ url('/') }}/api/auth/register/verify"
                    hx-target="#api-results">@csrf
                    {{-- Hack due to setTimout, we don't show the response --}}
                    <div id="api-results" class="hidden"></div>

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

                        <!-- Password Strength Meter -->
                        <div class="m-1 h-1 bg-gray-100 rounded-md overflow-hidden">
                            <div class="password-strength-indicator h-full transition-all duration-300 ease-out"
                                style="width: 0%">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="passwordConfirmation" class="block mb-2 text-sm font-medium">Confirm
                            Password</label>

                        <div class="relative">
                            <input type="password" name="passwordConfirmation" id="passwordConfirmation"
                                placeholder="******" required
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

                        <!-- Password Strength Meter -->
                        <div class="m-1 h-1 bg-gray-100 rounded-md overflow-hidden">
                            <div class="password-strength-indicator h-full transition-all duration-300 ease-out"
                                style="width: 0%">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="flex justify-between items-center align-middle gap-4">
                        <div class="loading-spinner hidden"></div>
                        <p class="error-message text-sm font-semibold text-red-500"></p>
                        <input type="submit" value="Complete registration"
                            class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full disabled:opacity-75 disabled:pointer-events-none" />
                    </div>
                </form>
            </div>

            <div id="success-message" class="hidden text-center m-auto max-w-md">
                <h3 class="mb-4 text-4xl font-bold text-brand-color">
                    Congratulations 🎉
                </h3>
                <p>Your all set, you can now navigate back to Classer app and login or <a
                        href="{{ url('/') }}?modal=download" class="text-brand-color underline">download it
                        here</a>.</p>
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
            if (evt.detail.successful != true) {
                document.querySelector(".loading-spinner").classList.add("hidden");
                document.querySelector("input[type=submit]").classList.remove("pointer-events-none");

                const errorElm = document.querySelector(".error-message");
                errorElm.innerHTML = [500, 401].includes(evt.detail.xhr.status) ? res.message :
                    "Something went wrong, please try again.";
                errorElm.classList.remove("hidden");
                return;
            }

            document.querySelector("#success-message").classList.remove("hidden");
            document.querySelector("#form").classList.add("hidden");
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

        const passwordStrengthIndicator = document.querySelectorAll('.password-strength-indicator');
        passwordStrengthIndicator.forEach((indicator) => {
            const passwordInput = indicator.parentElement.parentElement.querySelector('input');
            passwordInput.addEventListener('input', (e) => {
                const password = e.target.value;
                const criteria = validatePassword(password);
                const totalCriteria = Object.values(criteria).filter((c) => c).length;
                const strength = totalCriteria * 20;

                const colors = ['bg-red-500', 'bg-red-500', 'bg-yellow-500', 'bg-yellow-500',
                    'bg-green-500'
                ];

                indicator.style.width = `${strength}%`;
                indicator.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500');
                indicator.classList.add(colors[totalCriteria - 1]);
            });
        });

        const passwordInputs = document.querySelectorAll('form input[type="password"]');
        passwordInputs.forEach((passwordInput) => {
            passwordInput.addEventListener('input', (e) => {
                const match = passwordInputs[0].value === passwordInputs[1].value;
                const minChar = [...passwordInputs].every((input) => {
                    return input.value.length >= 6;
                });

                (match && minChar) ?
                document.querySelector('form input[type="submit"]').removeAttribute('disabled'):
                    document.querySelector('form input[type="submit"]').setAttribute('disabled',
                        'disabled');
            });
        });
    });

    function validatePassword(password) {
        const criteria = {
            hasLowercase: /[a-z]/.test(password),
            hasUppercase: /[A-Z]/.test(password),
            hasNumber: /[0-9]/.test(password),
            hasSpecialChar: /[ `!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/.test(password),
            hasMinChars: password.length >= 6
        };

        return criteria;
    }
</script>
