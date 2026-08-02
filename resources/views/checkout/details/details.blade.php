<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout Details</title>

    @include('partials.meta')

    @vite('resources/views/checkout/details/details.css')
</head>

<body class="checkout-details-page">
    @include('partials.navigation')

    <main class="checkout-details">
        <div class="checkout-details__layout">
            <section class="checkout-details__form-column">
                <form
                    action="{{ route('checkout.details.store') }}"
                    method="POST"
                    class="checkout-form"
                >
                    @csrf

                    @if ($errors->has('payment') || $errors->has('checkout'))
                        <div class="checkout-form__status checkout-form__status--error" role="alert">
                            {{ $errors->first('payment') ?: $errors->first('checkout') }}
                        </div>
                    @endif

                    <section class="checkout-form__section">
                        <header class="checkout-form__header">
                            <h1 class="checkout-form__title">
                                Your details
                            </h1>

                            <p class="checkout-form__description">
                                We'll use this information to send your order confirmation.
                            </p>
                        </header>

                        <div class="checkout-form__grid checkout-form__grid--two-columns">
                            <div class="checkout-field">
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
                                    value="{{ old('customer_name', $order->customer_name) }}"
                                    class="checkout-field__control"
                                    required
                                >

                                @error('customer_name')
                                    <p class="checkout-field__error">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="checkout-field">
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
                                    value="{{ old('customer_email', $order->customer_email) }}"
                                    class="checkout-field__control"
                                    required
                                >

                                @error('customer_email')
                                    <p class="checkout-field__error">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="checkout-form__section">
                        <header class="checkout-form__header">
                            <h2 class="checkout-form__title checkout-form__title--accent">
                                Delivery address
                            </h2>

                            <p class="checkout-form__description">
                                We'll use this address to deliver your order.
                            </p>
                        </header>

                        <div class="checkout-form__fields">
                            <div class="checkout-field">
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
                                    value="{{ old('shipping_line_1', $order->shipping_line_1) }}"
                                    class="checkout-field__control"
                                    required
                                >

                                @error('shipping_line_1')
                                    <p class="checkout-field__error">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="checkout-field">
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
                                    value="{{ old('shipping_line_2', $order->shipping_line_2) }}"
                                    class="checkout-field__control"
                                >

                                @error('shipping_line_2')
                                    <p class="checkout-field__error">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="checkout-form__grid checkout-form__grid--two-columns">
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
                                        value="{{ old('shipping_city', $order->shipping_city) }}"
                                        class="checkout-field__control"
                                        required
                                    >

                                    @error('shipping_city')
                                        <p class="checkout-field__error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="checkout-field">
                                    <label
                                        for="shipping_state"
                                        class="checkout-field__label"
                                    >
                                        State / County
                                    </label>

                                    <input
                                        id="shipping_state"
                                        name="shipping_state"
                                        type="text"
                                        value="{{ old('shipping_state', $order->shipping_state) }}"
                                        class="checkout-field__control"
                                    >

                                    @error('shipping_state')
                                        <p class="checkout-field__error">
                                            {{ $message }}
                                        </p>
                                    @enderror
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
                                        value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                        class="checkout-field__control"
                                        required
                                    >

                                    @error('shipping_postal_code')
                                        <p class="checkout-field__error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="checkout-field">
                                    <label
                                        for="shipping_country"
                                        class="checkout-field__label"
                                    >
                                        Country
                                    </label>

                                    <select
                                        id="shipping_country"
                                        name="shipping_country"
                                        class="checkout-field__control"
                                        required
                                    >
                                        @php
                                            $defaultCountryCode = strtoupper((string) (($countries[0]['code'] ?? 'GB')));
                                            $selectedCountry = strtoupper((string) old(
                                                'shipping_country',
                                                $order->shipping_country ?: $defaultCountryCode
                                            ));
                                        @endphp

                                        @foreach (($countries ?? []) as $country)
                                            <option
                                                value="{{ $country['code'] }}"
                                                @selected($selectedCountry === $country['code'])
                                            >
                                                {{ $country['name'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('shipping_country')
                                        <p class="checkout-field__error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        class="checkout-form__section checkout-form__section--discount"
                        x-data="{
                            open: {{ $errors->has('discount_code') || session('checkout_status') ? 'true' : 'false' }}
                        }"
                    >
                        <button
                            type="button"
                            class="checkout-form__toggle"
                            @click="open = ! open"
                            :aria-expanded="open.toString()"
                            aria-controls="discount-code-content"
                        >
                            <span class="checkout-form__toggle-content">
                                <span class="checkout-form__title">
                                    Discount code
                                </span>
                            </span>

                            <svg
                                class="checkout-form__toggle-icon"
                                :class="{ 'checkout-form__toggle-icon--open': open }"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    d="m6 9 6 6 6-6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <div
                            id="discount-code-content"
                            class="checkout-form__discount-content"
                            x-show="open"
                            x-transition:enter="checkout-form__discount-transition"
                            x-transition:enter-start="checkout-form__discount-transition--hidden"
                            x-transition:enter-end="checkout-form__discount-transition--visible"
                            x-transition:leave="checkout-form__discount-transition"
                            x-transition:leave-start="checkout-form__discount-transition--visible"
                            x-transition:leave-end="checkout-form__discount-transition--hidden"
                            x-cloak
                        >
                            <span class="checkout-form__description">
                                Have a discount code? Enter it below.
                            </span>

                            @if (session('checkout_status'))
                                <p class="checkout-form__status">
                                    {{ session('checkout_status') }}
                                </p>
                            @endif

                            <div class="checkout-form__discount">
                                <div class="checkout-field checkout-field--grow">
                                    <label
                                        for="discount_code"
                                        class="checkout-field__label checkout-field__label--visually-hidden"
                                    >
                                        Discount code
                                    </label>

                                    <input
                                        id="discount_code"
                                        name="discount_code"
                                        type="text"
                                        value="{{ old('discount_code', $order->discount_snapshot['code'] ?? '') }}"
                                        placeholder="Enter code"
                                        class="checkout-field__control"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    name="form_action"
                                    value="apply_discount"
                                    class="checkout-form__discount-button"
                                >
                                    Apply
                                </button>
                            </div>

                            @error('discount_code')
                                <p class="checkout-field__error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </section>

                    <div class="checkout-form__actions">
                        <button
                            type="submit"
                            name="form_action"
                            value="continue"
                            class="checkout-form__submit"
                        >
                            Continue to payment
                        </button>
                    </div>
                </form>
            </section>

            <aside class="checkout-details__summary-column">
                @include('checkout.partials.summary.summary', [
                    'order' => $order,
                    'showShippingEstimate' => true,
                ])
            </aside>
        </div>
    </main>

    <div class="my-8 md:my-12"></div>

    <section>
        <div class="w-full px-4 md:px-6">
            <div class="mx-auto w-full max-w-7xl">
                @include('partials.footer')
            </div>
        </div>
    </section>
</body>
</html>