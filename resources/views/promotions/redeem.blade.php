<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer - Redeem Promotion</title>
    @include('partials.shared.meta')
</head>

<body>
    @include('partials.shared.navigation')

    <article tabindex="-1" class="overflow-hidden w-full h-screen flex justify-center items-center"
        style="background-color: rgb(10 64 77); height: calc(100vh - 64px);">

        @include('partials.shared.triangles')

        <div class="relative px-6 py-16 bg-white rounded-lg shadow w-11/12 max-w-2xl">
            <div class="text-center mb-8 m-auto max-w-md">
                <h3 class="mb-4 text-4xl font-bold text-brand-color">
                    Redeem your promotion
                </h3>
                <p>Enter your checkout email and redeem code to activate your offer.</p>
            </div>

            <form id="redeem-form" class="space-y-6 m-auto max-w-md" method="POST"
                action="{{ route('promotions.redeem.submit') }}">
                @csrf

                <div>
                    <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                    <input type="email" name="email" id="email" required value="{{ $prefillEmail ?? '' }}"
                        class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400" />
                </div>

                <div>
                    <label for="redeem_code" class="block mb-2 text-sm font-medium">Redeem code</label>
                    <input type="text" name="redeem_code" id="redeem_code" required value="{{ $prefillRedeemCode ?? '' }}"
                        maxlength="64"
                        class="px-4 py-2 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400" />
                </div>

                <div class="flex justify-between items-center align-middle gap-4">
                    <div id="redeem-loading" class="loading-spinner hidden"></div>
                    <p id="redeem-message" class="text-sm font-semibold hidden"></p>
                    <button id="redeem-submit" type="submit"
                        class="btn inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white rounded-full disabled:opacity-75 disabled:pointer-events-none">
                        Redeem
                    </button>
                </div>
            </form>
        </div>
    </article>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('redeem-form');
        const submit = document.getElementById('redeem-submit');
        const loading = document.getElementById('redeem-loading');
        const message = document.getElementById('redeem-message');

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            loading.classList.remove('hidden');
            submit.classList.add('pointer-events-none');
            message.classList.add('hidden');
            message.classList.remove('text-red-500', 'text-green-600');

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let payload = null;
                try {
                    payload = await response.json();
                } catch (_error) {
                    payload = {
                        status: 'invalid',
                        message: 'Something went wrong. Please try again.',
                    };
                }

                const isSuccess = payload.status === 'redeemed';
                message.textContent = payload.message || 'Something went wrong. Please try again.';
                message.classList.add(isSuccess ? 'text-green-600' : 'text-red-500');
                message.classList.remove('hidden');
            } catch (_error) {
                message.textContent = 'Network error. Please try again.';
                message.classList.add('text-red-500');
                message.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
                submit.classList.remove('pointer-events-none');
            }
        });
    });
</script>

</html>