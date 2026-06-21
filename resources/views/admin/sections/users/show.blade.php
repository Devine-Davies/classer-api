@extends('admin.layout')

@php
    $activeSection = 'users';

    $subscriptions = $subscriptions ?? collect();
    $cloudShares = $cloudShares ?? collect();

    $cardClass = 'rounded-[0.85rem] border border-[#dce6ef] bg-white shadow-sm overflow-hidden';
    $cardHeaderClass = 'px-5 pt-5 pb-3';
    $cardBodyClass = 'px-5 pb-5';
    $cardFooterClass = 'border-t border-[#edf2f6] bg-[#f8fafc] px-5 py-4 text-sm text-[#64748b]';

    $labelClass = 'text-[0.82rem] text-[#64748b]';
    $valueClass = 'text-[0.92rem] font-medium text-[#263445]';

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-3 px-4 border-b border-[#e2eaf0] whitespace-nowrap';
    $tdClass = 'py-3 px-4 border-b border-[#edf2f6] text-sm text-[#334155] align-top';

    $formatDate = function ($value) {
        if (empty($value)) {
            return '—';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d M Y, H:i');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y, H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $formatBoolean = function ($value) {
        return $value ? 'Yes' : 'No';
    };

    $formatBytes = function ($bytes) {
        if ($bytes === null || $bytes === '') {
            return '—';
        }

        $bytes = (float) $bytes;

        if ($bytes < 1024) {
            return number_format($bytes, 0) . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value = $value / 1024;
            $unitIndex++;
        }

        return number_format($value, 2) . ' ' . $units[$unitIndex];
    };

    $totalCloudShareSize = $cloudShares->sum(function ($share) {
        return (int) ($share->size ?? 0);
    });

    $activeSubscriptionsCount = $subscriptions->filter(function ($subscription) {
        return ($subscription->status ?? null) === 'active';
    })->count();

    $userPlanLabel = data_get($user, 'plan.title')
        ?? data_get($user, 'plan.code')
        ?? data_get($user, 'plan_id')
        ?? '—';

    $accountStatusRaw = $user->accountStatus ?? null;
    $accountStatus = $user->accountStatusLabel ?? ucfirst(str_replace('_', ' ', (string) $accountStatusRaw ?? '-'));
    $accountStatusClass = match (strtolower((string) $accountStatus)) {
        'active', 'verified', 'enabled' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'blocked', 'banned', 'disabled', 'inactive' => 'border-rose-200 bg-rose-50 text-rose-700',
        'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };
@endphp

@section('content')
    <div class="max-w-[1100px]">
        <nav class="mb-8 text-sm text-[#64748b]">
            <a href="{{ url('/admin') }}" class="hover:text-[#0f172a]">Admin</a>
            <span class="mx-2 text-[#cbd5e1]">/</span>
            <a href="{{ url('/admin/users') }}" class="hover:text-[#0f172a]">Users</a>
            <span class="mx-2 text-[#cbd5e1]">/</span>
            <span class="font-semibold text-[#0f172a]">Details</span>
        </nav>

        <header class="mb-8">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="m-0 text-[1.75rem] font-bold leading-tight text-[#020617]">
                    User: {{ $user->name ?? 'Unnamed User' }}
                </h1>

                <span class="pill {{ $accountStatusClass }}">
                    {{ $accountStatus }}
                </span>
            </div>

            <p class="mt-3 text-[0.95rem] text-[#64748b]">
                {{ $formatDate($user->createdAt ?? null) }}
                @if (! empty($user->email))
                    from <span class="font-bold text-[#0f172a]">{{ $user->email }}</span>
                @endif
            </p>
        </header>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <section class="{{ $cardClass }} mb-8">
            <div class="{{ $cardHeaderClass }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="m-0 text-base font-bold text-[#020617]">User Details</h2>

                        <span class="pill {{ $accountStatusClass }}">
                            {{ $accountStatus }}
                        </span>
                    </div>

                    <a
                        href="{{ url('/admin/users') }}"
                        class="rounded-lg border border-[#d9e4ec] bg-white px-3 py-2 text-sm font-semibold text-[#334155] no-underline shadow-sm hover:border-[#94a3b8]"
                    >
                        Back to users
                    </a>
                </div>

                <p class="mt-5 text-sm text-[#64748b]">
                    Review profile information, registration details, and current plan.
                </p>
            </div>

            <div class="{{ $cardBodyClass }}">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <div class="{{ $labelClass }}">User UID</div>
                            <div class="{{ $valueClass }} font-mono break-all">{{ $user->uid ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="{{ $labelClass }}">Name</div>
                            <div class="{{ $valueClass }}">{{ $user->name ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="{{ $labelClass }}">Email</div>
                            <div class="{{ $valueClass }}">
                                @if (! empty($user->email))
                                    <a href="mailto:{{ $user->email }}" class="text-[#2563eb] hover:underline">
                                        {{ $user->email }}
                                    </a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="{{ $labelClass }}">Registration</div>
                            <div class="{{ $valueClass }}">
                                {{ ! empty($user->registration_type) ? ucfirst(str_replace('_', ' ', $user->registration_type)) : '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="{{ $labelClass }}">Plan</div>
                            <div class="{{ $valueClass }}">{{ $userPlanLabel }}</div>
                        </div>

                        <div>
                            <div class="{{ $labelClass }}">Last Updated</div>
                            <div class="{{ $valueClass }}">{{ $formatDate($user->updatedAt ?? null) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ $cardFooterClass }}">
                User created {{ $formatDate($user->createdAt ?? null) }}.
            </div>
        </section>

        <section class="{{ $cardClass }} mb-8">
            <div class="{{ $cardHeaderClass }}">
                <h2 class="m-0 text-base font-bold text-[#020617]">Usage Summary</h2>

                <span class="mt-5 inline-flex rounded-md border border-[#dce6ef] bg-white px-3 py-1 text-xs font-bold text-[#334155]">
                    {{ number_format($cloudShares->count()) }} cloud shares
                </span>

                <p class="mt-5 text-sm text-[#64748b]">
                    Review storage usage, cloud share count, and subscription totals for this user.
                </p>
            </div>

            <div class="{{ $cardBodyClass }}">
                <div class="space-y-4">
                    <div class="grid grid-cols-[1fr_auto] gap-4">
                        <div class="{{ $labelClass }}">Cloud Shares</div>
                        <div class="{{ $valueClass }}">{{ number_format($cloudShares->count()) }}</div>
                    </div>

                    <div class="grid grid-cols-[1fr_auto] gap-4">
                        <div class="{{ $labelClass }}">Total Cloud Share Size</div>
                        <div class="{{ $valueClass }}">{{ $formatBytes($totalCloudShareSize) }}</div>
                    </div>

                    <div class="grid grid-cols-[1fr_auto] gap-4">
                        <div class="{{ $labelClass }}">Active Subscriptions</div>
                        <div class="{{ $valueClass }}">{{ number_format($activeSubscriptionsCount) }}</div>
                    </div>

                    <div class="grid grid-cols-[1fr_auto] gap-4">
                        <div class="{{ $labelClass }}">Total Subscriptions</div>
                        <div class="{{ $valueClass }}">{{ number_format($subscriptions->count()) }}</div>
                    </div>

                    <div class="border-t border-[#e2e8f0] pt-4">
                        <div class="grid grid-cols-[1fr_auto] gap-4">
                            <div class="font-bold text-[#020617]">Current Plan</div>
                            <div class="font-bold text-[#020617]">{{ $userPlanLabel }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ $cardFooterClass }}">
                Includes {{ number_format($subscriptions->count()) }} subscription record{{ $subscriptions->count() === 1 ? '' : 's' }}.
            </div>
        </section>

        <section class="{{ $cardClass }} mb-8">
            <div class="{{ $cardHeaderClass }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="m-0 text-base font-bold text-[#020617]">Subscriptions</h2>

                    <span class="text-sm font-semibold text-[#64748b]">
                        {{ number_format($subscriptions->count()) }} total
                    </span>
                </div>

                <p class="mt-5 text-sm text-[#64748b]">
                    Subscription records associated with this user.
                </p>
            </div>

            <div class="{{ $cardBodyClass }}">
                <div class="overflow-x-auto rounded-xl border border-[#e2eaf0]">
                    <table class="w-full min-w-[920px] border-collapse">
                        <thead>
                            <tr class="bg-[#f8fafc]">
                                <th class="{{ $thClass }}">Subscription UID</th>
                                <th class="{{ $thClass }}">Plan</th>
                                <th class="{{ $thClass }}">Status</th>
                                <th class="{{ $thClass }}">Auto Renew</th>
                                <th class="{{ $thClass }}">Auto Renew Date</th>
                                <th class="{{ $thClass }}">Expires</th>
                                <th class="{{ $thClass }}">Cancelled</th>
                                <th class="{{ $thClass }}">Transaction</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($subscriptions as $subscription)
                                @php
                                    $subscriptionPlanLabel = data_get($subscription, 'plan.title')
                                        ?? data_get($subscription, 'plan.code')
                                        ?? data_get($subscription, 'plan_id')
                                        ?? '—';

                                    $status = $subscription->status ?? 'unknown';

                                    $statusClass = match ($status) {
                                        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'cancelled', 'canceled' => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'expired' => 'border-amber-200 bg-amber-50 text-amber-700',
                                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                                    };
                                @endphp

                                <tr class="hover:bg-[#f8fafc]">
                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs text-[#64748b]">
                                            {{ $subscription->uid ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-semibold text-[#0f172a]">{{ $subscriptionPlanLabel }}</span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatBoolean($subscription->auto_renew ?? false) }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($subscription->auto_renew_date ?? null) }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($subscription->expiration_date ?? null) }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($subscription->cancellation_date ?? null) }}

                                        @if (! empty($subscription->cancellation_reason))
                                            <p class="mt-1 text-xs text-[#64748b]">
                                                {{ $subscription->cancellation_reason }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs text-[#64748b]">
                                            {{ $subscription->transaction_id ?? '—' }}
                                        </span>
                                    </td>
                                </tr>

                                @if (! empty($subscription->notes))
                                    <tr>
                                        <td class="border-b border-[#edf2f6] bg-[#f8fafc] px-4 py-3 text-sm text-[#64748b]" colspan="8">
                                            <span class="font-bold text-[#334155]">Notes:</span>
                                            {{ $subscription->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td class="px-4 py-10 text-center text-sm text-[#64748b]" colspan="8">
                                        No subscriptions found for this user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="{{ $cardFooterClass }}">
                This user has {{ number_format($activeSubscriptionsCount) }} active subscription{{ $activeSubscriptionsCount === 1 ? '' : 's' }}.
            </div>
        </section>

        <section class="{{ $cardClass }}">
            <div class="{{ $cardHeaderClass }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="m-0 text-base font-bold text-[#020617]">Cloud Share Items</h2>

                    <span class="text-sm font-semibold text-[#64748b]">
                        {{ number_format($cloudShares->count()) }} total · {{ $formatBytes($totalCloudShareSize) }}
                    </span>
                </div>

                <p class="mt-5 text-sm text-[#64748b]">
                    Cloud share records associated with this user.
                </p>
            </div>

            <div class="{{ $cardBodyClass }}">
                <div class="overflow-x-auto rounded-xl border border-[#e2eaf0]">
                    <table class="w-full min-w-[760px] border-collapse">
                        <thead>
                            <tr class="bg-[#f8fafc]">
                                <th class="{{ $thClass }}">Cloud Share UID</th>
                                <th class="{{ $thClass }}">Resource ID</th>
                                <th class="{{ $thClass }}">Size</th>
                                <th class="{{ $thClass }}">Created</th>
                                <th class="{{ $thClass }}">Updated</th>
                                <th class="{{ $thClass }}">Deleted</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cloudShares as $cloudShare)
                                <tr class="hover:bg-[#f8fafc]">
                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs text-[#64748b]">
                                            {{ $cloudShare->uid ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs text-[#64748b]">
                                            {{ $cloudShare->resource_id ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-semibold text-[#0f172a]">
                                            {{ $formatBytes($cloudShare->size ?? null) }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($cloudShare->created_at ?? null) }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($cloudShare->updated_at ?? null) }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatDate($cloudShare->deleted_at ?? null) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-10 text-center text-sm text-[#64748b]" colspan="6">
                                        No cloud share items found for this user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="{{ $cardFooterClass }}">
                Total cloud share usage is {{ $formatBytes($totalCloudShareSize) }}.
            </div>
        </section>
    </div>
@endsection