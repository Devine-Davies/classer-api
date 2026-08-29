@extends('admin.layout')

@php
    $activeSection = 'users';
    $subscriptions = collect($user->subscriptions ?? []);
    $cloudShares = collect($cloudShares ?? []);

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

    $totalCloudShareSize = $cloudShares->sum(fn ($share) => (int) ($share->size ?? 0));
    $activeSubscriptions = $subscriptions->where('status', 'active')->count();
    $accountStatus = $user->accountStatusLabel ?? 'Unknown';
    $isVerified = strcasecmp($accountStatus, 'Verified') === 0;
    $accountState = $isVerified ? 'Active' : $accountStatus;
    $statusTone = match (strtolower($accountStatus)) {
        'verified' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'inactive' => 'border-amber-300 bg-amber-100 text-amber-800',
        'suspended' => 'border-amber-200 bg-amber-50 text-amber-700',
        'deactivated' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };
    $isDeactivated = strcasecmp($accountStatus, 'Deactivated') === 0;
    $uid = (string) ($user->uid ?? '');
    $shortUid = strlen($uid) > 16 ? substr($uid, 0, 8) . '…' . substr($uid, -4) : ($uid ?: '—');
    $tabs = ['overview' => 'Overview', 'subscriptions' => 'Subscriptions', 'cloud-share' => 'Cloud Share', 'orders' => 'Orders', 'activity' => 'Activity'];
    $emailActions = array_values(array_filter([
        $isVerified ? ['label' => 'Password reset', 'template' => 'password_reset'] : null,
        ! $isVerified ? ['label' => 'Account verification', 'template' => 'account_verification'] : null,
        $isVerified ? ['label' => 'Welcome email', 'template' => 'welcome'] : null,
    ]));
    $cardClass = 'border border-[#dce6ef] bg-white shadow-sm';
    $thClass = 'border-b border-[#e2eaf0] px-4 py-3 text-left text-[0.72rem] font-bold uppercase tracking-[0.04em] text-[#647384] whitespace-nowrap';
    $tdClass = 'border-b border-[#edf2f6] px-4 py-3 text-sm text-[#334155] align-top';
@endphp

@section('content')
    <div
        class="w-full max-w-[1100px]"
        x-data="{
            activeTab: 'overview',
            actionsOpen: false,
            copied: false,
            tabs: @js(array_keys($tabs)),
            init() {
                const requestedTab = window.location.hash.replace('#', '');
                if (this.tabs.includes(requestedTab)) this.activeTab = requestedTab;
            },
            selectTab(tab) {
                this.activeTab = tab;
                history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${tab}`);
            },
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

        <section class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4" aria-label="User metrics">
            @foreach ([['Cloud shares', number_format($cloudShares->count())], ['Storage used', $formatBytes($totalCloudShareSize)], ['Subscriptions', number_format($activeSubscriptions)], ['Account', $accountState]] as [$label, $value])
                <div class="rounded-lg border border-[#dce6ef] bg-white px-4 py-4 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.08em] text-[#8491a1]">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-extrabold leading-none text-[#0f172a]">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <nav class="admin-tabs-scrollbar mt-5 flex gap-6 overflow-x-auto border-b border-[#dce6ef]" aria-label="User details">
            @foreach ($tabs as $tab => $label)
                <button type="button" class="-mb-px whitespace-nowrap border-b-2 px-0.5 pb-3 text-sm font-semibold transition" x-on:click="selectTab('{{ $tab }}')" x-bind:class="activeTab === '{{ $tab }}' ? 'border-emerald-600 text-[#0f172a]' : 'border-transparent text-[#64748b] hover:text-[#334155]'" x-bind:aria-selected="activeTab === '{{ $tab }}'" role="tab">
                    {{ $label }}
                    @if ($tab === 'subscriptions' && $subscriptions->isNotEmpty()) <span class="ml-1 text-xs text-[#64748b]">{{ $subscriptions->count() }}</span>
                    @elseif ($tab === 'cloud-share' && $cloudShares->isNotEmpty()) <span class="ml-1 text-xs text-[#64748b]">{{ $cloudShares->count() }}</span> @endif
                </button>
            @endforeach
        </nav>

        <div class="mt-4">
            <section x-show="activeTab === 'overview'" x-cloak class="grid gap-4 lg:grid-cols-[minmax(0,1.25fr)_minmax(280px,0.75fr)]" role="tabpanel">
                <article class="{{ $cardClass }} rounded-lg">
                    <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">User information</h2></div>
                    <dl class="grid gap-x-8 gap-y-3 px-5 py-4 sm:grid-cols-[7rem_1fr]">
                        <dt class="text-sm text-[#64748b]">Name</dt><dd class="text-sm font-semibold text-[#1e293b]">{{ $user->name ?? '—' }}</dd>
                        <dt class="text-sm text-[#64748b]">Email</dt><dd class="break-all text-sm font-semibold text-[#1e293b]">{{ $user->email ?? '—' }}</dd>
                        <dt class="text-sm text-[#64748b]">User ID</dt><dd class="break-all font-mono text-xs font-semibold text-[#1e293b]">{{ $uid ?: '—' }}</dd>
                        <dt class="text-sm text-[#64748b]">Created</dt><dd class="text-sm font-semibold text-[#1e293b]">{{ $formatDate($user->createdAt ?? null) }}</dd>
                    </dl>
                </article>

                <article class="{{ $cardClass }} rounded-lg">
                    <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">Account status</h2></div>
                    <dl class="grid grid-cols-[1fr_auto] gap-x-5 gap-y-3 px-5 py-4">
                        <dt class="text-sm text-[#64748b]">Email verified</dt><dd class="text-sm font-semibold text-[#1e293b]">{{ $isVerified ? 'Yes' : 'No' }}</dd>
                        <dt class="text-sm text-[#64748b]">Last login</dt><dd class="text-sm font-semibold text-[#64748b]">Never signed in</dd>
                        <dt class="text-sm text-[#64748b]">Last active</dt><dd class="text-right text-sm font-semibold text-[#64748b]">No activity recorded</dd>
                        <dt class="self-center text-sm text-[#64748b]">Status</dt><dd><span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-bold {{ $statusTone }}">{{ $accountStatus }}</span></dd>
                    </dl>
                </article>

                <article class="{{ $cardClass }} rounded-lg">
                    <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">Recent activity</h2></div>
                    <div class="flex min-h-24 items-center px-5 py-5">
                        <div><p class="text-sm font-semibold text-[#334155]">No recent activity</p><p class="mt-1 text-xs text-[#7b8794]">User activity will appear here when tracking is available.</p></div>
                    </div>
                </article>

                <article class="{{ $cardClass }} rounded-lg">
                    <div class="border-b border-[#edf2f6] px-5 py-3.5"><h2 class="text-base font-bold text-[#0f172a]">Quick actions</h2></div>
                    <div class="grid gap-2 p-3">
                        @include('admin.users.partials.email-options', [
                            'buttonClass' => 'flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-semibold text-[#334155] transition hover:bg-[#f1f5f9]',
                            'menuAlign' => 'left-0',
                        ])
                        @include('admin.partials.confirm-delete-form', [
                            'formId' => 'quick-toggle-user-account-form', 'action' => route('admin.users.deactivate', ['userUid' => $uid]), 'method' => 'POST',
                            'title' => $isDeactivated ? 'Reactivate account' : 'Deactivate account',
                            'description' => $isDeactivated ? 'This will restore access to the account and allow the user to sign in again.' : 'This will disable the account and invalidate the password for this user.',
                            'buttonLabel' => $isDeactivated ? 'Reactivate account' : 'Deactivate account', 'confirmValue' => $isDeactivated ? 'REACTIVATE' : 'DEACTIVATE',
                            'confirmLabel' => $isDeactivated ? 'Type REACTIVATE to confirm' : 'Type DEACTIVATE to confirm', 'modalTitle' => $isDeactivated ? 'Confirm reactivation' : 'Confirm deactivation',
                            'compact' => true, 'buttonClass' => 'w-full rounded-md px-3 py-2 text-left text-sm font-semibold text-[#334155] transition hover:bg-[#f1f5f9]',
                        ])
                        <button type="button" class="w-full rounded-md px-3 py-2 text-left text-sm font-semibold text-[#334155] transition hover:bg-[#f1f5f9]" x-on:click="selectTab('cloud-share')">View cloud share</button>
                    </div>
                </article>
            </section>

            <section x-show="activeTab === 'subscriptions'" x-cloak class="{{ $cardClass }}" role="tabpanel">
                <div class="flex items-center justify-between border-b border-[#edf2f6] px-5 py-4"><h2 class="text-base font-bold text-[#0f172a]">Subscriptions</h2><span class="text-xs font-semibold text-[#64748b]">{{ number_format($subscriptions->count()) }} total</span></div>
                @if ($subscriptions->isEmpty())
                    <div class="flex min-h-24 items-center justify-center px-5 py-6 text-sm text-[#64748b]">No subscriptions</div>
                @else
                    <div class="overflow-x-auto"><table class="w-full min-w-[880px] border-collapse">
                        <thead><tr class="bg-[#f8fafc]"><th class="{{ $thClass }}">Plan</th><th class="{{ $thClass }}">Status</th><th class="{{ $thClass }}">Expires</th><th class="{{ $thClass }}">Auto renew</th><th class="{{ $thClass }}">Cancelled</th><th class="{{ $thClass }}">Transaction</th></tr></thead>
                        <tbody>
                            @foreach ($subscriptions as $subscription)
                                @php
                                    $status = data_get($subscription, 'status', 'unknown');
                                    $subscriptionStatusClass = match ($status) {
                                        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'cancelled', 'canceled' => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'expired' => 'border-amber-200 bg-amber-50 text-amber-700',
                                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                                    };
                                @endphp
                                <tr class="hover:bg-[#f8fafc]">
                                    <td class="{{ $tdClass }} font-semibold text-[#0f172a]">{{ data_get($subscription, 'plan.title') ?? data_get($subscription, 'plan.code') ?? data_get($subscription, 'planId') ?? '—' }}</td>
                                    <td class="{{ $tdClass }}"><span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $subscriptionStatusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                                    <td class="{{ $tdClass }}">{{ $formatDate(data_get($subscription, 'expirationDate')) }}</td><td class="{{ $tdClass }}">{{ $formatDate(data_get($subscription, 'autoRenewDate')) }}</td>
                                    <td class="{{ $tdClass }}">{{ $formatDate(data_get($subscription, 'cancellationDate')) }}</td><td class="{{ $tdClass }} font-mono text-xs">{{ data_get($subscription, 'transactionId') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>

            <section x-show="activeTab === 'cloud-share'" x-cloak class="{{ $cardClass }}" role="tabpanel">
                <div class="flex items-center justify-between border-b border-[#edf2f6] px-5 py-4"><h2 class="text-base font-bold text-[#0f172a]">Cloud Share Items</h2><span class="text-xs font-semibold text-[#64748b]">{{ number_format($cloudShares->count()) }} total · {{ $formatBytes($totalCloudShareSize) }}</span></div>
                @if ($cloudShares->isEmpty())
                    <div class="flex min-h-24 items-center justify-center px-5 py-6 text-sm text-[#64748b]">No cloud share items</div>
                @else
                    <div class="overflow-x-auto"><table class="w-full min-w-[760px] border-collapse">
                        <thead><tr class="bg-[#f8fafc]"><th class="{{ $thClass }}">Cloud Share UID</th><th class="{{ $thClass }}">Resource ID</th><th class="{{ $thClass }}">Size</th><th class="{{ $thClass }}">Created</th><th class="{{ $thClass }}">Updated</th><th class="{{ $thClass }}">Deleted</th></tr></thead>
                        <tbody>
                            @foreach ($cloudShares as $cloudShare)
                                <tr class="hover:bg-[#f8fafc]"><td class="{{ $tdClass }} font-mono text-xs">{{ $cloudShare->uid ?? '—' }}</td><td class="{{ $tdClass }} font-mono text-xs">{{ $cloudShare->resource_id ?? '—' }}</td><td class="{{ $tdClass }} font-semibold text-[#0f172a]">{{ $formatBytes($cloudShare->size ?? null) }}</td><td class="{{ $tdClass }}">{{ $formatDate($cloudShare->created_at ?? null) }}</td><td class="{{ $tdClass }}">{{ $formatDate($cloudShare->updated_at ?? null) }}</td><td class="{{ $tdClass }}">{{ $formatDate($cloudShare->deleted_at ?? null) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </section>

            <section x-show="activeTab === 'orders'" x-cloak class="{{ $cardClass }}" role="tabpanel">
                <div class="border-b border-[#edf2f6] px-5 py-4"><h2 class="text-base font-bold text-[#0f172a]">Orders</h2></div>
                <div class="flex min-h-24 flex-wrap items-center justify-between gap-3 px-5 py-5"><p class="text-sm text-[#64748b]">Open the orders workspace filtered to this user's email.</p><a href="{{ route('admin.orders', ['q' => $user->email ?? '']) }}" class="admin-btn admin-btn-neutral">View orders</a></div>
            </section>

            <section x-show="activeTab === 'activity'" x-cloak class="{{ $cardClass }}" role="tabpanel">
                <div class="border-b border-[#edf2f6] px-5 py-4"><h2 class="text-base font-bold text-[#0f172a]">Activity</h2></div>
                <div class="flex min-h-24 items-center justify-center px-5 py-6 text-sm text-[#64748b]">No activity is available for this user.</div>
            </section>
        </div>
    </div>
@endsection