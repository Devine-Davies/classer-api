<!DOCTYPE html>

<html lang="en">

<head>
    <title>Classer - Register</title>
    @include('partials.shared.meta')
    @vite('resources/css/markdown/main.css')
</head>

<body>
    @include('partials.shared.naviagtion')

    <article tabindex="-1"
        class="hero-bg hero-bg__bottom overflow-y-auto overflow-x-hidden w-full h-screen flex justify-center items-center"
        style="height: calc(100vh - 64px);">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-brand-color to-brand-color"
            style="background-color: rgb(10 64 77 / 72%); backdrop-filter: blur(44px);"></div>
        <div class="relative px-6 py-16 bg-white rounded-lg shadow w-full max-w-2xl">
            <div id="register-form" class="">
                <div class="text-center mb-8 m-auto max-w-md">
                    <h3 class="mb-4 text-4xl font-bold text-brand-color">
                        Forgot your password?
                    </h3>
                    <p>Enter your email address and we'll send you a link to reset your accounts password.</p>
                </div>

                <form 
                    class="space-y-6 m-auto max-w-md" 
                    hx-post="{{ url('/') }}/api/auth/forgot-password"
                    hx-target="#api-results"
                >@csrf
                    {{-- Hack due to setTimout, we don't show the response --}}
                    <div id="api-results" class="hidden" ></div>

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" name="email" id="email" value=""
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                            placeholder="yourEmail@example.com" required />
                    </div>

                    <div class="flex justify-between items-center align-middle gap-12">
                        <div class="loading-spinner hidden"></div>
                        <p class="form-error-msg text-sm font-semibold text-red-500"></p>
                        <input type="submit" value="Send reset link"
                            class="btn py-2 px-5 text-white rounded-full cursor-pointer" />
                    </div>
                </form>
            </div>

            <div id="register-success" class="hidden text-center m-auto max-w-md">
                <h3 class="mb-4 text-4xl font-bold text-brand-color">
                    Check your inbox 📬
                </h3>
                <p>We've sent an email to <span class="users-email font-semibold"></span> with a link to reset your password. From there you can follow that link to reset your password.</p>
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

        // get the email from the form
        const email = document.getElementById("email").value;
        document.querySelector(".users-email").innerHTML = email;

        setTimeout(() => {
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
        }, 1000);
    });
</script>