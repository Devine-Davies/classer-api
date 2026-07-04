<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout Payment</title>
    @include('partials.meta')
    @vite('resources/js/checkout.js')
</head>

@php
   // dd($checkoutDraft);
@endphp

<body class="antialiased bg-off-white">
    @include('partials.navigation')

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
                            <p class="mt-1 text-sm leading-tight text-slate-400">
                                Review your order and continue to secure payment.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h2 class="text-base font-semibold text-slate-700">
                            Customer details
                        </h2>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Name</dt>
                                <dd class="font-medium text-slate-700">
                                    {{ $checkoutDraft->customer_name ?? 'Not provided' }}
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Email</dt>
                                <dd class="font-medium text-slate-700">
                                    {{ $checkoutDraft->customer_email ?? 'Not provided' }}
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Shipping address</dt>
                                <dd class="text-right font-medium text-slate-700">
                                    {{ $checkoutDraft->shipping_line_1 ?? '' }}

                                    @if (! empty($checkoutDraft->shipping_line_2))
                                        <br>{{ $checkoutDraft->shipping_line_2 }}
                                    @endif

                                    <br>
                                    {{ $checkoutDraft->shipping_city ?? '' }}
                                    {{ $checkoutDraft->shipping_state ?? '' }}
                                    {{ $checkoutDraft->shipping_postal_code ?? '' }}

                                    <br>
                                    {{ $checkoutDraft->shipping_country ?? '' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6 rounded-xl border border-slate-100 bg-white p-4">
                        <h2 class="text-base font-semibold text-slate-700">
                            Payment method
                        </h2>

                        <div class="mt-6 rounded-xl">
                            <label class="hidden text-base font-semibold text-slate-600">Card details</label>
                            <div id="payment-element" class="mt-3"></div>
                            <p id="payment-message" class="mt-3 text-sm text-red-600"></p>
                        </div>
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

            @include('checkout.partials.summary', [
                'order' => $checkoutDraft,
            ])
        </div>
    </main>

        <script>
        window.checkoutConfig = {
            stripeClientSecret: "{{ $stripeClientSecret }}",
            stripePublishableKey: "{{ $stripePublishableKey }}",
            successUrl: "{{ route('checkout.success', ['orderUid' => $order->uid]) }}",
            orderDetails: @json($order),
        };
    </script>
</body>
</html>