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
        @include('checkout.partials.steps', ['step' => $step])

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <section class="lg:col-span-3 rounded-2xl bg-white p-6 md:p-8 shadow-sm border border-slate-100">
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Payment</h1>
                <p class="mt-2 text-sm text-slate-600">Review your saved details and complete payment securely with Stripe.</p>

                {{-- <div class="mt-8 rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Contact</p>
                            <p class="mt-2 text-sm font-medium text-slate-900">{{ $order->customer_name }}</p>
                            <p class="text-sm text-slate-600">{{ $order->customer_email }}</p>
                        </div>
                        <a href="{{ route('checkout.details') }}" class="text-sm font-medium text-brand-color underline underline-offset-4">Edit</a>
                    </div>
                </div> --}}

                {{-- <div class="mt-4 rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Delivery</p>
                            <div class="mt-2 text-sm text-slate-700">
                                <p>{{ $order->shipping_line_1 }}</p>
                                @if (filled($order->shipping_line_2))
                                    <p>{{ $order->shipping_line_2 }}</p>
                                @endif
                                <p>{{ $order->shipping_city }}{{ filled($order->shipping_state) ? ', ' . $order->shipping_state : '' }}</p>
                                <p>{{ $order->shipping_postal_code }}</p>
                                <p>{{ strtoupper($order->shipping_country) }}</p>
                            </div>
                        </div>
                        <a href="{{ route('checkout.delivery') }}" class="text-sm font-medium text-brand-color underline underline-offset-4">Edit</a>
                    </div>
                </div> --}}

                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-700">Card details</label>
                    <div id="payment-element"></div>
                    <p id="payment-message" class="mt-3 text-sm text-red-600"></p>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('checkout.delivery') }}" class="text-sm font-medium text-slate-500 underline underline-offset-4">Back to delivery</a>
                    <button id="pay-btn" type="button" class="btn w-full sm:w-auto">Pay now</button>
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