<!DOCTYPE html>
<html lang="en">

<head>
    <title>Order Confirmed</title>
    @include('partials.shared.meta')
</head>

<body class="antialiased bg-off-white">
    @include('partials.shared.navigation')

    <main class="mx-auto max-w-3xl px-4 py-14">
        <section class="rounded-2xl bg-white p-8 shadow-sm border border-slate-100 text-center">
            <p class="text-xs uppercase tracking-[0.16em] text-emerald-600">Payment received</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Thanks, your order is confirmed</h1>
            <p class="mt-3 text-slate-600">Order UID: {{ $order->uid }}</p>

            <div class="mt-8 rounded-xl border border-slate-200 p-5 text-left">
                <h2 class="text-lg font-semibold text-slate-900">Order details</h2>
                <dl class="mt-4 grid gap-2 text-sm text-slate-700">
                    <div class="flex justify-between"><dt>Product</dt><dd>{{ $order->product?->name }}</dd></div>
                    <div class="flex justify-between"><dt>Total</dt><dd>{{ strtoupper($order->currency) }} {{ number_format($order->amount / 100, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Status</dt><dd class="capitalize">{{ $order->status }}</dd></div>
                </dl>
            </div>

            <a href="{{ url('/') }}" class="btn inline-block mt-8">Back to home</a>
        </section>
    </main>
</body>

</html>
