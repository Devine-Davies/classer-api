<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout Details</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-14">
        <div class="grid gap-6 lg:grid-cols-5">
            <section class="lg:col-span-3">
                <form action="{{ route('checkout.details.store') }}" method="POST" class="gap-6 flex flex-col">
                    @csrf

                    <section class="rounded-2xl bg-white p-5">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-md">
                                @icon('user')
                            </div>
                            <div>
                                <h1 class="text-xl font-semibold leading-tight">Your details</h1>
                                <p class="mt-1 text-sm leading-tight text-slate-400">We'll use this information to send
                                    your order confirmation.</p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500" for="customer_name">Full
                                    name</label>
                                <input id="customer_name" name="customer_name" type="text" required
                                    value="{{ old('customer_name', $order->customer_name) }}"
                                    class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                @error('customer_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500"
                                    for="customer_email">Email</label>
                                <input id="customer_email" name="customer_email" type="email" required
                                    value="{{ old('customer_email', $order->customer_email) }}"
                                    class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                @error('customer_email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-5">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center">
                                @icon('location')
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold leading-tight text-[#1a4b59]">Delivery address</h2>
                                <p class="mt-1 text-sm leading-tight text-slate-400">We'll use this address to deliver
                                    your order.</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500"
                                    for="shipping_line_1">Address line 1</label>
                                <input id="shipping_line_1" name="shipping_line_1" type="text" required
                                    value="{{ old('shipping_line_1', $order->shipping_line_1) }}"
                                    class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                @error('shipping_line_1')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500"
                                    for="shipping_line_2">Address line 2 (optional)</label>
                                <input id="shipping_line_2" name="shipping_line_2" type="text"
                                    value="{{ old('shipping_line_2', $order->shipping_line_2) }}"
                                    class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                @error('shipping_line_2')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500"
                                        for="shipping_city">City</label>
                                    <input id="shipping_city" name="shipping_city" type="text" required
                                        value="{{ old('shipping_city', $order->shipping_city) }}"
                                        class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                    @error('shipping_city')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500"
                                        for="shipping_state">State / County</label>
                                    <input id="shipping_state" name="shipping_state" type="text"
                                        value="{{ old('shipping_state', $order->shipping_state) }}"
                                        class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                    @error('shipping_state')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500"
                                        for="shipping_postal_code">Postal code</label>
                                    <input id="shipping_postal_code" name="shipping_postal_code" type="text" required
                                        value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                        class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                                    @error('shipping_postal_code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500"
                                        for="shipping_country">Country</label>
                                    <select id="shipping_country" name="shipping_country" required
                                        class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-brand-color focus:outline-none">
                                        <option value="GB" @selected(old('shipping_country', $order->shipping_country ?: 'GB') === 'GB')>United Kingdom</option>
                                    </select>
                                    @error('shipping_country')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-5">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center">
                                @icon('tag')
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold leading-tight">Discount code</h2>
                                <p class="mt-1 text-sm leading-tight text-slate-400">Have a discount code? Enter it
                                    below.</p>
                            </div>
                        </div>

                        @if (session('checkout_status'))
                            <p class="mt-3 text-sm font-medium text-emerald-700">{{ session('checkout_status') }}</p>
                        @endif

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <input id="discount_code" name="discount_code" type="text"
                                value="{{ old('discount_code', $order->discount_snapshot['code'] ?? '') }}"
                                placeholder="Enter code"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-color focus:outline-none">
                            <button type="submit" name="form_action" value="apply_discount"
                                class="inline-flex h-[42px] items-center justify-center rounded-lg bg-[#79b887] px-6 text-sm font-semibold text-white transition hover:bg-[#68a976]">Apply</button>
                        </div>
                        @error('discount_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <div class="flex flex-col gap-3 py-2 sm:flex-row sm:items-center sm:justify-end">
                        <button type="submit" name="form_action" value="continue"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#0e4f62] px-8 py-3 text-sm font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-[#0a4253] sm:w-auto">
                            <span>Continue to payment</span>
                            @icon('lock')
                        </button>
                    </div>
                </form>
            </section>

            @include('checkout.partials.summary', ['order' => $order])
        </div>
    </main>
</body>

</html>
