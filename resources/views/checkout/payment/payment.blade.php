<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout Payment</title>

    @include('partials.meta')

    @vite('resources/views/checkout/payment/payment.css')
    @vite('resources/js/checkout.js')
</head>

<body class="checkout-payment-page">
    @include('partials.navigation')

    <main class="checkout-payment">
        <div class="checkout-payment__layout">
            <section class="checkout-payment__content">
                <div class="checkout-payment__panel">
                    <header class="checkout-payment__header">
                        <h1 class="checkout-payment__title">
                            Payment
                        </h1>

                        <p class="checkout-payment__description">
                            Review your order and continue to secure payment.
                        </p>
                    </header>

                    <section class="checkout-payment__section checkout-payment__section--compact">
                        <div class="checkout-payment__section-header">
                            <h2 class="checkout-payment__section-title">
                                Your details
                            </h2>

                            <a
                                href="{{ route('checkout.details') }}"
                                class="checkout-payment__section-action"
                            >
                                Edit
                            </a>
                        </div>

                        <dl class="checkout-details-list">
                            <div class="checkout-details-list__row">
                                <dt class="checkout-details-list__term">
                                    Name
                                </dt>

                                <dd class="checkout-details-list__value">
                                    {{ $checkoutDraft->customer_name ?? 'Not provided' }}
                                </dd>
                            </div>

                            <div class="checkout-details-list__row">
                                <dt class="checkout-details-list__term">
                                    Email
                                </dt>

                                <dd class="checkout-details-list__value">
                                    {{ $checkoutDraft->customer_email ?? 'Not provided' }}
                                </dd>
                            </div>

                            <div class="checkout-details-list__row">
                                <dt class="checkout-details-list__term">
                                    Address
                                </dt>

                                <dd class="checkout-details-list__value">
                                    {{ collect([
                                        $checkoutDraft->shipping_line_1 ?? null,
                                        $checkoutDraft->shipping_line_2 ?? null,
                                        $checkoutDraft->shipping_city ?? null,
                                        $checkoutDraft->shipping_state ?? null,
                                        $checkoutDraft->shipping_postal_code ?? null,
                                        $checkoutDraft->shipping_country ?? null,
                                    ])->filter()->implode(', ') ?: 'Not provided' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="checkout-payment__section">
                        <h2 class="checkout-payment__section-title">
                            Payment method
                        </h2>

                        <div class="checkout-card">
                            <h3 class="checkout-card__title">
                                Card details
                            </h3>

                            <div
                                id="payment-element"
                                class="checkout-card__element"
                            ></div>

                            <p
                                id="payment-message"
                                class="checkout-card__message"
                                role="alert"
                            ></p>
                        </div>
                    </section>
                </div>

                <div class="checkout-payment__actions">
                    <span></span>

                    <button
                        id="pay-btn"
                        type="button"
                        class="checkout-payment__submit"
                    >
                        <span>Pay now</span>
                        @icon('lock')
                    </button>
                </div>
            </section>

            <aside class="checkout-payment__summary">
                @include('checkout.partials.summary.summary', [
                    'order' => $order,
                ])
            </aside>
        </div>
    </main>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('partials.footer')
            </div>
        </div>
    </section>

    <script>
        window.checkoutConfig = {
            stripeClientSecret: @js($stripeClientSecret),
            stripePublishableKey: @js($stripePublishableKey),
            successUrl: @js(route('checkout.success', [
                'orderUid' => $order->uid,
            ])),
            orderDetails: @js($order),
        };
    </script>
</body>
</html>