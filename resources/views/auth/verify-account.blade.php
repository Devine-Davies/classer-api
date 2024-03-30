<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - Register</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body>
    @include('partials.shared.naviagtion')

    <article tabindex="-1" class="hero-bg hero-bg__bottom overflow-y-auto overflow-x-hidden w-full h-screen flex justify-center items-center"
        style="height: calc(100vh - 64px);">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-brand-color to-brand-color"
        style="background-color: rgb(10 64 77 / 72%); backdrop-filter: blur(44px);"
        ></div>
        <div class="relative px-6 py-16 bg-white rounded-lg shadow w-full max-w-2xl">
            <div id="register-form" class="">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Almost there!
                    </h3>
                    <p>Last step, create a password for <span class="font-semibold">{{ $userEmail }}</span> to verify your account and start using Classer.</p>
                </div>

                <form class="space-y-6 m-auto max-w-md" 
                    hx-post="{{ url('/') }}/api/auth/verify-registration"
                    hx-indicator="#spinner">
                    {{-- hx-on="htmx:afterRequest: onRegisterSuccess();" --}}
                    @csrf
            
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                        <input type="text" name="password" id="password" value="myPassword"
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            placeholder="Jane Doe" required />
                    </div>

                    <div>
                        <label for="passwordConfirmation" class="block mb-2 text-sm font-medium">Confirm Password</label>
                        <input type="text" name="passwordConfirmation" id="passwordConfirmation" value="myPassword"
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            placeholder="yourEmail@example.com" required />
                    </div>

                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="flex justify-between items-center align-middle gap-4">
                        <div class="loading-spinner hidden"></div>
                        <p class="form-error-msg text-sm font-semibold text-red-500"></p>
                        <input type="submit" value="Complete registration"
                            class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full" />
                    </div>
                </form>
            </div>

            <div id="register-success" class="hidden text-center m-auto max-w-md">
                <h3 class="mb-4 text-4xl font-bold text-brand-color">
                    Congratulations!
                </h3>
                <p>Your all set, you can now navigate back to Classer app and login.</p>
            </div>
        </div>
    </article>
</body>

</html>


<script>
    document.addEventListener('htmx:beforeRequest', (evt) => {
        document.querySelector(".loading-spinner").classList.remove("hidden");
        document.querySelector(".form-error-msg").classList.add("hidden");
        document.querySelector("input[type=submit]").classList.add("pointer-events-none");

        google.recaptcha.execute('6LeT-wwmAAAAAL64va5W33XKEhALIBLnjeDv_FtL', {
            action: 'register'
        }).then(function(token) {
            document.getElementById('register-form').insertAdjacentHTML('beforeend',
                '<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
        });
    });

    document.addEventListener('htmx:afterRequest', (evt) => {
        const res = JSON.parse(evt.detail.xhr.response);
        if (evt.detail.successful != true) {
            document.querySelector(".loading-spinner").classList.add("hidden");
            document.querySelector("input[type=submit]").classList.remove("pointer-events-none");

            const errorElm = document.querySelector(".form-error-msg");
            errorElm.innerHTML = [500, 401].includes(evt.detail.xhr.status) ? res.message : "Something went wrong, please try again.";
            errorElm.classList.remove("hidden");
            return;
        }

        document.getElementById("register-success").classList.remove("hidden");
        document.getElementById("register-form").classList.add("hidden");
    });
</script>