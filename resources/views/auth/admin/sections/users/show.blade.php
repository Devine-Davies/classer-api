@extends('auth.admin.layout')

@php
    $activeSection = 'users';

    $subscriptions = $subscriptions ?? collect();
    $cloudShares = $cloudShares ?? collect();

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0] whitespace-nowrap';
    $tdClass = 'py-[0.72rem] px-[0.9rem] border-b border-[#edf2f6] text-sm text-slate-700 align-top';
    $dtClass = 'text-xs font-bold uppercase tracking-[0.04em] text-slate-500';
    $ddClass = 'text-sm text-slate-800 break-words';

    $formatDate = function ($value) {
        if (empty($value)) {
            return '—';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d H:i');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
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
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">User Overview</h2>
        <p class="mt-[0.35rem] text-admin-muted">
            Review user details, cloud usage, subscription history, and cloud share activity.
        </p>
    </header>

    <section class="border border-admin-stroke bg-white p-4">
        <div class="flex items-center justify-between gap-3 flex-wrap border-b border-[#e6edf3] pb-[0.8rem]">
            <div>
                <p class="m-0 text-slate-500 text-[0.74rem] tracking-[0.04em] uppercase font-bold">
                    User UID
                </p>

                <h3 class="mt-1 text-[#1f2d39] text-base font-mono">
                    {{ $user->uid ?? '—' }}
                </h3>
            </div>

            <a
                href="{{ url('/auth/admin/users') }}"
                class="border border-[#d9e4ec] rounded-[0.6rem] py-[0.45rem] px-[0.7rem] text-slate-700 no-underline text-[0.82rem] font-semibold bg-white hover:border-slate-400"
            >
                Back to users
            </a>
        </div>

        @if (session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-[0.8rem] mt-[0.8rem] lg:grid-cols-2">
            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Details</h4>

                <dl class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]">
                    <dt class="{{ $dtClass }}">Name</dt>
                    <dd class="{{ $ddClass }}">{{ $user->name ?? '—' }}</dd>

                    <dt class="{{ $dtClass }}">Email</dt>
                    <dd class="{{ $ddClass }}">
                        @if (! empty($user->email))
                            <a href="mailto:{{ $user->email }}" class="text-[var(--admin-primary)] hover:underline">
                                {{ $user->email }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="{{ $dtClass }}">Account Status</dt>
                    <dd class="{{ $ddClass }}">
                        @if (! empty($user->account_status))
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                {{ ucfirst(str_replace('_', ' ', $user->account_status->name ?? 'inactive')) }}
                            </span>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="{{ $dtClass }}">Registration</dt>
                    <dd class="{{ $ddClass }}">
                        {{ ! empty($user->registration_type) ? ucfirst(str_replace('_', ' ', $user->registration_type)) : '—' }}
                    </dd>

                    <dt class="{{ $dtClass }}">Plan</dt>
                    <dd class="{{ $ddClass }}">{{ $userPlanLabel }}</dd>

                    <dt class="{{ $dtClass }}">Created</dt>
                    <dd class="{{ $ddClass }}">{{ $formatDate($user->created_at ?? null) }}</dd>

                    <dt class="{{ $dtClass }}">Updated</dt>
                    <dd class="{{ $ddClass }}">{{ $formatDate($user->updated_at ?? null) }}</dd>
                </dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem]">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Cloud Usage</h4>

                <dl class="mt-[0.6rem] grid grid-cols-[150px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]">
                    <dt class="{{ $dtClass }}">Cloud Shares</dt>
                    <dd class="{{ $ddClass }}">{{ number_format($cloudShares->count()) }}</dd>

                    <dt class="{{ $dtClass }}">Total Size</dt>
                    <dd class="{{ $ddClass }}">{{ $formatBytes($totalCloudShareSize) }}</dd>

                    <dt class="{{ $dtClass }}">Active Subs</dt>
                    <dd class="{{ $ddClass }}">{{ number_format($activeSubscriptionsCount) }}</dd>

                    <dt class="{{ $dtClass }}">Total Subs</dt>
                    <dd class="{{ $ddClass }}">{{ number_format($subscriptions->count()) }}</dd>
                </dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Subscriptions</h4>

                    <span class="text-xs font-semibold text-slate-500">
                        {{ number_format($subscriptions->count()) }} total
                    </span>
                </div>

                <div class="mt-[0.6rem] overflow-x-auto">
                    <table class="w-full border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-[#eef3f7]">
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
                                        'active' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled', 'canceled' => 'bg-rose-50 text-rose-700',
                                        'expired' => 'bg-amber-50 text-amber-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <tr class="hover:bg-slate-50">
                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs">
                                            {{ $subscription->uid ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $subscriptionPlanLabel }}
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">
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
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $subscription->cancellation_reason }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs">
                                            {{ $subscription->transaction_id ?? '—' }}
                                        </span>
                                    </td>
                                </tr>

                                @if (! empty($subscription->notes))
                                    <tr>
                                        <td class="{{ $tdClass }} bg-slate-50" colspan="8">
                                            <span class="font-semibold text-slate-600">Notes:</span>
                                            {{ $subscription->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td class="{{ $tdClass }} text-center text-slate-500" colspan="8">
                                        No subscriptions found for this user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Cloud Share Items</h4>

                    <span class="text-xs font-semibold text-slate-500">
                        {{ number_format($cloudShares->count()) }} total · {{ $formatBytes($totalCloudShareSize) }}
                    </span>
                </div>

                <div class="mt-[0.6rem] overflow-x-auto">
                    <table class="w-full border-collapse min-w-[760px]">
                        <thead>
                            <tr class="bg-[#eef3f7]">
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
                                <tr class="hover:bg-slate-50">
                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs">
                                            {{ $cloudShare->uid ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        <span class="font-mono text-xs">
                                            {{ $cloudShare->resource_id ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="{{ $tdClass }}">
                                        {{ $formatBytes($cloudShare->size ?? null) }}
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
                                    <td class="{{ $tdClass }} text-center text-slate-500" colspan="6">
                                        No cloud share items found for this user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
@endsection