<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout Details</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-7xl px-4 py-10 md:py-14">
        @include('checkout.partials.steps', ['step' => $step])

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <section class="lg:col-span-3 rounded-2xl bg-white p-6 md:p-8 shadow-sm border border-slate-100">
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Your details</h1>
                <p class="mt-2 text-sm text-slate-600">Tell us who this order is for before we move on to delivery.</p>

                <form action="{{ route('checkout.details.store') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="customer_name">Full name</label>
                        <input id="customer_name" name="customer_name" type="text" required value="{{ old('customer_name', $order->customer_name) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        @error('customer_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="customer_email">Email</label>
                        <input id="customer_email" name="customer_email" type="email" required value="{{ old('customer_email', $order->customer_email) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-brand-color focus:outline-none">
                        @error('customer_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ url('/checkout') }}" class="text-sm font-medium text-slate-500 underline underline-offset-4">Cancel checkout</a>
                        <button type="submit" class="btn w-full sm:w-auto">Continue to delivery</button>
                    </div>
                </form>
            </section>

            @include('checkout.partials.summary', ['order' => $order])
        </div>
    </main>
</body>

</html>