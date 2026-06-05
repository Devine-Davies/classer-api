<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout Delivery</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-14">
        @include('checkout.partials.steps', ['step' => $step])

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <section class="lg:col-span-3 rounded-2xl bg-white p-6 md:p-8 shadow-sm border border-slate-100">
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Delivery address</h1>
                <p class="mt-2 text-sm text-slate-600">Enter where this order should be sent. We’ll use this on the payment step.</p>

                <form action="{{ route('checkout.delivery.store') }}" method="POST" class="mt-8 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="shipping_line_1">Address line 1</label>
                        <input id="shipping_line_1" name="shipping_line_1" type="text" required value="{{ old('shipping_line_1', $order->shipping_line_1) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        @error('shipping_line_1')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="shipping_line_2">Address line 2 (optional)</label>
                        <input id="shipping_line_2" name="shipping_line_2" type="text" value="{{ old('shipping_line_2', $order->shipping_line_2) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        @error('shipping_line_2')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_city">City</label>
                            <input id="shipping_city" name="shipping_city" type="text" required value="{{ old('shipping_city', $order->shipping_city) }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                            @error('shipping_city')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_state">State/County</label>
                            <input id="shipping_state" name="shipping_state" type="text" value="{{ old('shipping_state', $order->shipping_state) }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                            @error('shipping_state')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_postal_code">Postal code</label>
                            <input id="shipping_postal_code" name="shipping_postal_code" type="text" required value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                            @error('shipping_postal_code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="shipping_country">Country</label>
                            <input id="shipping_country" name="shipping_country" type="text" required maxlength="2" value="{{ old('shipping_country', $order->shipping_country ?: 'GB') }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase focus:border-brand-color focus:outline-none">
                            @error('shipping_country')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('checkout.details') }}" class="text-sm font-medium text-slate-500 underline underline-offset-4">Back to details</a>
                        <button type="submit" class="btn w-full sm:w-auto">Continue to payment</button>
                    </div>
                </form>
            </section>

            @include('checkout.partials.summary', ['order' => $order])
        </div>
    </main>
</body>

</html>