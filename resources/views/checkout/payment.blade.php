<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout Payment</title>
    @include('partials.shared.meta')
    @vite('resources/js/checkout.js')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-14">
        <div class="grid gap-6 lg:grid-cols-5">
            <section class="lg:col-span-3">
                <div class="rounded-2xl bg-white p-5 md:p-6">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-500">
                            @icon('card')
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold leading-tight">Payment</h1>
                            <p class="mt-1 text-sm leading-tight text-slate-400">Add your card details to complete the purchase.</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl">
                        <label class="hidden text-base font-semibold text-slate-600">Card details</label>
                        <div id="payment-element" class="mt-3"></div>
                        <p id="payment-message" class="mt-3 text-sm text-red-600"></p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('checkout.details') }}" class="text-sm font-medium text-slate-400">Back</a>
                    <button id="pay-btn" type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#0e4f62] px-8 py-3 text-sm font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-[#0a4253] sm:w-auto">
                        <span>Pay now</span>
                        @icon('lock')
                    </button>
                </div>
            </section>

            @include('checkout.partials.summary', ['order' => $order])
        </div>
    </main>

    <script>
        window.checkoutConfig = {
            orderUid: "{{ $order->uid }}",
            stripePublishableKey: "{{ $stripePublishableKey }}",
            paymentIntentUrl: "{{ '/api/checkout/orders/' . $order->uid . '/intent' }}",
            successUrl: window.location.origin + "{{ '/checkout/' . $order->uid . '/success' }}",
            orderDetails: {
                customer_name: @json($order->customer_name),
                customer_email: @json($order->customer_email),
                shipping_line_1: @json($order->shipping_line_1),
                shipping_line_2: @json($order->shipping_line_2),
                shipping_city: @json($order->shipping_city),
                shipping_state: @json($order->shipping_state),
                shipping_postal_code: @json($order->shipping_postal_code),
                shipping_country: @json($order->shipping_country),
            }
        };
    </script>
</body>

</html>
