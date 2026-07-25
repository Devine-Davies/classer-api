<!DOCTYPE html>
<html lang="en">
<head>
    <title>Classer Secure Checkout</title>

    @include('partials.meta')

    @vite('resources/views/checkout/index/index.css')
    @vite('resources/js/checkout.js')
</head>

<body class="checkout-page">
    @include('partials.navigation')

    <main class="checkout">
        <div class="checkout__layout">
            <section class="checkout__content">
                <header class="checkout__header">
                    <h1 class="checkout__title">
                        Secure Checkout
                    </h1>

                    <p class="checkout__description">
                        Complete your shipping details and payment.
                    </p>
                </header>

                {{-- Step 1: Shipping and contact --}}
                <form
                    id="shipping-form"
                    class="checkout-form"
                    novalidate
                >
                    <div class="checkout-form__grid">
                        <div class="checkout-field checkout-field--full">
                            <label
                                for="customer_name"
                                class="checkout-field__label"
                            >
                                Full name
                            </label>

                            <input
                                id="customer_name"
                                name="customer_name"
                                type="text"
                                class="checkout-field__control"
                                required
                            >
                        </div>

                        <div class="checkout-field checkout-field--full">
                            <label
                                for="customer_email"
                                class="checkout-field__label"
                            >
                                Email
                            </label>

                            <input
                                id="customer_email"
                                name="customer_email"
                                type="email"
                                class="checkout-field__control"
                                required
                            >
                        </div>

                        <div class="checkout-field checkout-field--full">
                            <label
                                for="shipping_line_1"
                                class="checkout-field__label"
                            >
                                Address line 1
                            </label>

                            <input
                                id="shipping_line_1"
                                name="shipping_line_1"
                                type="text"
                                class="checkout-field__control"
                                required
                            >
                        </div>

                        <div class="checkout-field checkout-field--full">
                            <label
                                for="shipping_line_2"
                                class="checkout-field__label"
                            >
                                Address line 2 (optional)
                            </label>

                            <input
                                id="shipping_line_2"
                                name="shipping_line_2"
                                type="text"
                                class="checkout-field__control"
                            >
                        </div>

                        <div class="checkout-field">
                            <label
                                for="shipping_city"
                                class="checkout-field__label"
                            >
                                City
                            </label>

                            <input
                                id="shipping_city"
                                name="shipping_city"
                                type="text"
                                class="checkout-field__control"
                                required
                            >
                        </div>

                        <div class="checkout-field">
                            <label
                                for="shipping_state"
                                class="checkout-field__label"
                            >
                                State/County
                            </label>

                            <input
                                id="shipping_state"
                                name="shipping_state"
                                type="text"
                                class="checkout-field__control"
                            >
                        </div>

                        <div class="checkout-field">
                            <label
                                for="shipping_postal_code"
                                class="checkout-field__label"
                            >
                                Postal code
                            </label>

                            <input
                                id="shipping_postal_code"
                                name="shipping_postal_code"
                                type="text"
                                class="checkout-field__control"
                                required
                            >
                        </div>

                        <div class="checkout-field">
                            <label
                                for="shipping_country"
                                class="checkout-field__label"
                            >
                                Country (2 letters)
                            </label>

                            <input
                                id="shipping_country"
                                name="shipping_country"
                                type="text"
                                maxlength="2"
                                value="GB"
                                class="checkout-field__control checkout-field__control--uppercase"
                                required
                            >
                        </div>
                    </div>

                    <p
                        id="shipping-message"
                        class="checkout-form__message checkout-form__message--error"
                        role="alert"
                    ></p>

                    <button
                        id="continue-btn"
                        type="submit"
                        class="checkout-form__submit"
                    >
                        Continue to Payment
                    </button>
                </form>

                {{-- Step 2: Card payment --}}
                <section
                    id="payment-section"
                    class="checkout-payment checkout-payment--hidden"
                    aria-labelledby="payment-heading"
                >
                    <header class="checkout-payment__header">
                        <h2
                            id="payment-heading"
                            class="checkout-payment__title"
                        >
                            Payment details
                        </h2>

                        <button
                            id="edit-shipping-btn"
                            type="button"
                            class="checkout-payment__edit"
                        >
                            Edit shipping
                        </button>
                    </header>

                    <div
                        id="payment-element"
                        class="checkout-payment__element"
                    ></div>

                    <p
                        id="payment-message"
                        class="checkout-payment__message checkout-payment__message--error"
                        role="alert"
                    ></p>

                    <button
                        id="pay-btn"
                        type="button"
                        class="checkout-payment__submit"
                    >
                        Pay Now
                    </button>
                </section>
            </section>

            <aside class="checkout__summary">
                @include('checkout.partials.summary.summary', [
                    'order' => $order,
                ])
            </aside>
        </div>
    </main>

    <script>
        window.checkoutConfig = {
            orderUid: @js($order->uid),
            stripePublishableKey: @js($stripePublishableKey),
            paymentIntentUrl: @js('/api/checkout/orders/' . $order->uid . '/intent'),
            successUrl: window.location.origin + @js('/checkout/' . $order->uid . '/success'),
        };
    </script>
</body>
</html>