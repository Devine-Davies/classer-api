<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Secure Checkout</title>
    @include('partials.shared.meta')
    @vite('resources/js/checkout.js')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-14">
        <div class="grid gap-8 lg:grid-cols-5">
            <section class="lg:col-span-3 rounded-2xl bg-white p-6 md:p-8 shadow-sm border border-slate-100">
                <h1 class="text-2xl font-semibold text-slate-900">Secure Checkout</h1>
                <p class="mt-2 text-sm text-slate-600">Complete your shipping details and payment.</p>

                {{-- Step 1: Shipping & contact --}}
                <form id="shipping-form" class="mt-8 space-y-4" novalidate>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="customer_name">Full name</label>
                            <input id="customer_name" name="customer_name" type="text" required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="customer_email">Email</label>
                            <input id="customer_email" name="customer_email" type="email" required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="shipping_line_1">Address line 1</label>
                            <input id="shipping_line_1" name="shipping_line_1" type="text" required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="shipping_line_2">Address line 2 (optional)</label>
                            <input id="shipping_line_2" name="shipping_line_2" type="text"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_city">City</label>
                            <input id="shipping_city" name="shipping_city" type="text" required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_state">State/County</label>
                            <input id="shipping_state" name="shipping_state" type="text"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_postal_code">Postal code</label>
                            <input id="shipping_postal_code" name="shipping_postal_code" type="text" required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_country">Country (2 letters)</label>
                            <input id="shipping_country" name="shipping_country" type="text" maxlength="2" required value="GB"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase focus:border-brand-color focus:outline-none">
                        </div>
                    </div>

                    <p id="shipping-message" class="text-sm text-red-600"></p>

                    <button id="continue-btn" type="submit" class="btn w-full">Continue to Payment</button>
                </form>

                {{-- Step 2: Card payment (hidden until shipping is confirmed) --}}
                <div id="payment-section" class="hidden mt-6 space-y-4">
                    <div class="flex items-center gap-3 py-3 border-t border-slate-200">
                        <span class="text-sm font-medium text-slate-700">Payment details</span>
                        <button id="edit-shipping-btn" type="button" class="ml-auto text-xs text-brand-color underline">Edit shipping</button>
                    </div>
                    <div id="payment-element"></div>
                    <p id="payment-message" class="text-sm text-red-600"></p>
                    <button id="pay-btn" type="button" class="btn w-full">Pay Now</button>
                </div>
            </section>

            <aside class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm border border-slate-100 h-fit">
                <h2 class="text-lg font-semibold text-slate-900">Order summary</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="flex items-start justify-between gap-3">
                        <span>{{ $order->product?->name }}</span>
                        <span>x{{ $order->quantity }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                        <span class="font-medium">Total</span>
                        <span class="text-base font-semibold text-slate-900">
                            {{ strtoupper($order->currency) }} {{ number_format($order->amount / 100, 2) }}
                        </span>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        window.checkoutConfig = {
            orderUid: "{{ $order->uid }}",
            stripePublishableKey: "{{ $stripePublishableKey }}",
            paymentIntentUrl: "{{ '/api/checkout/orders/' . $order->uid . '/intent' }}",
            successUrl: window.location.origin + "{{ '/checkout/' . $order->uid . '/success' }}"
        };
    </script>
</body>

</html>
