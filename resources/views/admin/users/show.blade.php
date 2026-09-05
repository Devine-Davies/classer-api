@extends('admin.layout')

@php
    $uid = (string) ($user->uid ?? '');
    $activeSection = 'users';
    $subscriptions = collect($user->subscriptions ?? []);
    $cloudShareCount = (int) ($cloudShareCount ?? 0);

    $totalCloudShareSize = (int) data_get($user, 'cloudUsage.shareUsage', 0);
    $totalCloudBackupSize = (int) data_get($user, 'cloudUsage.backupUsage', 0);
    $accountStatus = $user->accountStatusLabel ?? 'Unknown';
    $isVerified = strcasecmp($accountStatus, 'Verified') === 0;
    $accountState = $isVerified ? 'Active' : $accountStatus;
    $isDeactivated = strcasecmp($accountStatus, 'Deactivated') === 0;
    $shortUid = strlen($uid) > 16 ? substr($uid, 0, 8) . '…' . substr($uid, -4) : ($uid ?: '—');
    $emailActions = array_values(array_filter([
        $isVerified ? ['label' => 'Password reset', 'template' => 'password_reset'] : null,
        ! $isVerified ? ['label' => 'Account verification', 'template' => 'account_verification'] : null,
        $isVerified ? ['label' => 'Welcome email', 'template' => 'welcome'] : null,
    ]));

    $cardClass = 'border border-[#dce6ef] bg-white shadow-sm';
    $statusTone = match (strtolower($accountStatus)) {
        'verified' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'inactive' => 'border-amber-300 bg-amber-100 text-amber-800',
        'suspended' => 'border-amber-200 bg-amber-50 text-amber-700',
        'deactivated' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };

    $formatDate = function ($value, $includeTime = true) {
        if (empty($value)) return '—';

        try {
            return \Carbon\Carbon::parse($value)->format($includeTime ? 'd M Y, H:i' : 'd M Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $formatBytes = function ($bytes) {
        $bytes = (float) ($bytes ?? 0);
        if ($bytes < 1024) return number_format($bytes, 0) . ' B';

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $value = $bytes / 1024;
        $unitIndex = 0;
        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, 2) . ' ' . $units[$unitIndex];
    };

    $formatDuration = function ($seconds) {
        if ($seconds === null) return 'Unlimited';

        $seconds = max(0, (int) $seconds);
        $units = [
            'year' => 365 * 24 * 60 * 60,
            'month' => 30 * 24 * 60 * 60,
            'day' => 24 * 60 * 60,
            'hour' => 60 * 60,
            'minute' => 60,
        ];

        foreach ($units as $label => $unitSeconds) {
            if ($seconds >= $unitSeconds && $seconds % $unitSeconds === 0) {
                $value = intdiv($seconds, $unitSeconds);

                return $value . ' ' . $label . ($value === 1 ? '' : 's');
            }
        }

        return number_format($seconds) . ' seconds';
    };
@endphp

@section('content')
    <div
        class="w-full max-w-[1100px]"
        x-data="{
            actionsOpen: false,
            copied: false,
            async copyUid() {
                try {
                    await navigator.clipboard.writeText(@js($uid));
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1600);
                } catch (error) {
                    this.copied = false;
                }
            }
        }"
        x-on:keydown.escape.window="actionsOpen = false"
    >
        <header class="rounded-lg border border-[#dce6ef] bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="m-0 text-[1.8rem] font-bold leading-tight text-[#020617]">{{ $user->name ?? 'Unnamed User' }}</h1>
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusTone }}">{{ $accountStatus }}</span>
                    </div>
                    <a href="mailto:{{ $user->email ?? '' }}" class="mt-1.5 inline-block text-sm font-medium text-[#334155] hover:text-[#2563eb] hover:underline">{{ $user->email ?? 'No email address' }}</a>
                    <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-[#64748b]">
                        <button type="button" class="inline-flex items-center gap-2 rounded-md font-mono transition hover:text-[#0f172a] focus:outline-none focus:ring-2 focus:ring-emerald-500/30" x-on:click="copyUid" title="Copy full user ID">
                            <span>{{ $shortUid }}</span><span class="font-sans text-sm" aria-hidden="true">⧉</span><span class="font-sans text-emerald-700" x-show="copied" x-cloak>Copied</span>
                        </button>
                        <span>Joined {{ $formatDate($user->createdAt ?? null, false) }}</span>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:mb-0.5 sm:ml-6">
                    @include('admin.users.partials.email-options', [
                        'buttonClass' => 'inline-flex items-center justify-center gap-2 rounded-lg border border-[#d9e4ec] bg-white px-3 py-2 text-sm font-semibold text-[#334155] shadow-sm transition hover:border-[#94a3b8] hover:bg-[#f8fafc]',
                        'menuAlign' => 'left-0',
                    ])
                    <div class="relative">
                        <button type="button" class="inline-flex h-9 w-10 items-center justify-center rounded-lg border border-[#d9e4ec] bg-white text-lg font-bold text-[#334155] shadow-sm transition hover:border-[#94a3b8] hover:bg-[#f8fafc]" x-on:click="actionsOpen = !actionsOpen" x-bind:aria-expanded="actionsOpen" aria-haspopup="menu" title="More actions">
                            <span aria-hidden="true">•••</span><span class="sr-only">More actions</span>
                        </button>
                        <div x-show="actionsOpen" x-cloak x-transition.origin.top.right x-on:click.outside="actionsOpen = false" class="absolute right-0 z-30 mt-2 w-52 border border-[#dce6ef] bg-white p-1.5 shadow-lg" role="menu">
                            @include('admin.partials.confirm-delete-form', [
                                'formId' => 'toggle-user-account-form', 'action' => route('admin.users.deactivate', ['userUid' => $uid]), 'method' => 'POST',
                                'title' => $isDeactivated ? 'Reactivate account' : 'Deactivate account',
                                'description' => $isDeactivated ? 'This will restore access to the account and allow the user to sign in again.' : 'This will disable the account and invalidate the password for this user.',
                                'buttonLabel' => $isDeactivated ? 'Reactivate account' : 'Deactivate account', 'confirmValue' => $isDeactivated ? 'REACTIVATE' : 'DEACTIVATE',
                                'confirmLabel' => $isDeactivated ? 'Type REACTIVATE to confirm' : 'Type DEACTIVATE to confirm', 'modalTitle' => $isDeactivated ? 'Confirm reactivation' : 'Confirm deactivation',
                                'compact' => true, 'buttonClass' => 'w-full rounded-md px-3 py-2 text-left text-sm font-semibold text-[#334155] transition hover:bg-[#f1f5f9]',
                            ])
                            <div class="mt-1 border-t border-[#e2e8f0] pt-1">
                                @include('admin.partials.confirm-delete-form', [
                                    'formId' => 'delete-user-form', 'action' => route('admin.users.destroy', ['userUid' => $uid]), 'method' => 'DELETE',
                                    'title' => 'Delete user', 'description' => 'This will permanently remove the user account and related data.', 'buttonLabel' => 'Delete user',
                                    'confirmValue' => 'DELETE', 'confirmLabel' => 'Type DELETE to confirm', 'modalTitle' => 'Confirm deletion', 'compact' => true,
                                    'buttonClass' => 'w-full rounded-md px-3 py-2 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="{{ $cardClass }} mt-5 rounded-lg">
            <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">Account status</h2></div>
            <dl class="grid gap-x-8 gap-y-3 px-5 py-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-sm text-[#64748b]">Email verified</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ $isVerified ? 'Yes' : 'No' }}</dd></div>
                <div><dt class="text-sm text-[#64748b]">Last login</dt><dd class="mt-1 text-sm font-semibold text-[#64748b]">Never signed in</dd></div>
                <div><dt class="text-sm text-[#64748b]">Last active</dt><dd class="mt-1 text-sm font-semibold text-[#64748b]">No activity recorded</dd></div>
                <div><dt class="text-sm text-[#64748b]">Status</dt><dd class="mt-1"><span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $statusTone }}">{{ $accountStatus }}</span></dd></div>
            </dl>
        </section>

        <section class="mt-5 space-y-4">
            <div class="flex items-center justify-between"><h2 class="text-base font-bold text-[#0f172a]">Subscriptions</h2><span class="text-xs font-semibold text-[#64748b]">{{ number_format($subscriptions->count()) }} total</span></div>
                @if ($subscriptions->isEmpty())
                    <div class="{{ $cardClass }} flex min-h-24 items-center justify-center rounded-lg px-5 py-6 text-sm text-[#64748b]">No subscriptions</div>
                @else
                    @foreach ($subscriptions as $subscription)
                        @php
                            $status = data_get($subscription, 'status', 'unknown');
                            $plan = data_get($subscription, 'plan');
                            $entitlements = collect(data_get($plan, 'entitlements', []));
                            $isCloudShareSubscription = strtolower((string) data_get($plan, 'type')) === 'cloud_share';
                        @endphp
                        <article class="{{ $cardClass }} overflow-hidden rounded-lg">
                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#edf2f6] px-5 py-4">
                                <div><h3 class="text-base font-bold text-[#0f172a]">{{ data_get($plan, 'title') ?? data_get($plan, 'code') ?? data_get($subscription, 'planId') ?? '—' }}</h3><p class="mt-1 font-mono text-xs text-[#64748b]">{{ data_get($plan, 'code') ?? 'No plan code' }}</p></div>
                                <div class="flex items-center gap-3">
                                    @if ($isCloudShareSubscription && filled($user->email ?? null))
                                        <a href="{{ route('admin.cloud-shares', ['q' => $user->email, 'state' => 'all']) }}" class="text-sm font-semibold text-[#2563eb] hover:underline">View Cloud Shares</a>
                                    @endif
                                </div>
                            </div>
                            <dl class="grid gap-4 border-b border-[#edf2f6] px-5 py-4 sm:grid-cols-2 lg:grid-cols-5">
                                <div><dt class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Type</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ data_get($plan, 'type') ?? '—' }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Duration</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ $formatDuration(data_get($plan, 'duration')) }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Started</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ $formatDate(data_get($subscription, 'createdAt')) }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Expires</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ $formatDate(data_get($subscription, 'expirationDate')) }}</dd></div>
                                <div><dt class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Auto renew</dt><dd class="mt-1 text-sm font-semibold text-[#1e293b]">{{ $formatDate(data_get($subscription, 'autoRenewDate')) }}</dd></div>
                            </dl>
                            <div class="px-5 py-4"><p class="text-xs font-bold uppercase tracking-[0.04em] text-[#64748b]">Entitlements and account usage</p>
                                <div class="mt-3 grid gap-4 md:grid-cols-2">
                                    @forelse ($entitlements as $entitlement)
                                        @php
                                            $capability = data_get($entitlement, 'capability');
                                            $quota = (int) data_get($entitlement, 'quota', 0);
                                            $usage = match ($capability) {
                                                'cloud_share' => $totalCloudShareSize,
                                                'cloud_backup' => $totalCloudBackupSize,
                                                default => 0,
                                            };
                                            $usagePercent = $quota > 0 ? min(100, round(($usage / $quota) * 100)) : 0;
                                        @endphp
                                        <div class="border-l-2 border-emerald-500 pl-3">
                                            <div class="flex items-baseline justify-between gap-3">
                                                <p class="text-sm font-semibold">{{ \Illuminate\Support\Str::headline($capability) }}</p>
                                            </div>
                                            <p class="mt-1 text-sm">
                                                {{ $formatBytes($usage) }}(<span class="font-semibold">{{ $usagePercent }}%</span>) used of {{ $formatBytes($quota) }} 
                                                </p>
                                        </div>
                                    @empty
                                        <p class="text-sm">No entitlement records are configured for this plan.</p>
                                    @endforelse
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endif
        </section>

        <section class="{{ $cardClass }} mt-5 rounded-lg">
            <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">Recent activity</h2></div>
            <div class="flex min-h-24 items-center px-5 py-5"><div><p class="text-sm font-semibold text-[#334155]">No recent activity</p><p class="mt-1 text-xs text-[#7b8794]">User activity will appear here when tracking is available.</p></div></div>
        </section>
    </div>
@endsection