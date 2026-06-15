@extends('auth.admin.layout')

@php
    $activeSection = 'users';
    $currentPage   = $pagination['current_page'] ?? 1;
    $lastPage      = $pagination['last_page'] ?? 1;
    $from          = $pagination['from'] ?? 0;
    $to            = $pagination['to'] ?? 0;
    $total         = $pagination['total'] ?? 0;

    $statusClasses = [
        'verified'    => 'is-verified',
        'suspended'   => 'is-suspended',
        'deactivated' => 'is-deactivated',
    ];

    $q               = $filters['q'] ?? request('q', '');
    $hasSubscription = $filters['has_subscription'] ?? request('has_subscription', 'all');
    $accountState    = $filters['account_state'] ?? request('account_state', 'all');

    $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    $tdClass = 'py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]';
@endphp

@section('content')
    <section class="border border-admin-stroke bg-white">
        <form method="GET" action=""
              class="flex items-center justify-between gap-3 px-4 py-[0.9rem] border-b border-[#e5edf3] bg-[#fbfdff]"
              id="users-filter-form">
            <div class="flex items-center gap-[0.65rem] flex-wrap">
                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="users-status-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Status</span>
                    <select id="users-status-filter" name="account_state"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('users-filter-form').submit()">
                        <option value="all"         @selected($accountState === 'all')>All</option>
                        <option value="inactive"    @selected($accountState === 'inactive')>Inactive</option>
                        <option value="verified"    @selected($accountState === 'verified')>Verified</option>
                        <option value="suspended"   @selected($accountState === 'suspended')>Suspended</option>
                        <option value="deactivated" @selected($accountState === 'deactivated')>Deactivated</option>
                    </select>
                </label>

                <label class="inline-flex items-center gap-[0.4rem] border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.55rem] min-w-[260px]"
                       for="users-search">
                    <span class="text-[#7b8794] text-[0.95rem] leading-none">⌕</span>
                    <input id="users-search" name="q" type="search" placeholder="Search by email"
                           class="border-0 outline-none w-full text-[#27343f] text-[0.88rem] bg-transparent"
                           value="{{ $q }}"
                           oninput="clearTimeout(window._usersSearchTimer); window._usersSearchTimer = setTimeout(() => document.getElementById('users-filter-form').submit(), 300)">
                </label>

                <label class="inline-flex items-center gap-2 border border-[#d8e2ea] rounded-[0.65rem] bg-white h-[2.35rem] px-[0.65rem]"
                       for="users-subscription-filter">
                    <span class="text-[0.76rem] font-bold tracking-[0.04em] uppercase text-[#6f7c89]">Subscription</span>
                    <select id="users-subscription-filter" name="has_subscription"
                            class="border-0 outline-none bg-transparent text-[#28343f] text-[0.88rem] font-semibold"
                            onchange="document.getElementById('users-filter-form').submit()">
                        <option value="all" @selected($hasSubscription === 'all')>All</option>
                        <option value="yes" @selected($hasSubscription === 'yes')>Has subscription</option>
                        <option value="no"  @selected($hasSubscription === 'no')>No subscription</option>
                    </select>
                </label>
            </div>

            <p class="m-0 text-[#66717a] text-[0.82rem] font-semibold">
                @if ($total)
                    {{ $from }}&ndash;{{ $to }} of {{ number_format($total) }}
                @else
                    0 results
                @endif
            </p>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#eef3f7]">
                        <th class="{{ $thClass }}">Status</th>
                        <th class="{{ $thClass }}">Email</th>
                        <th class="{{ $thClass }}">Name</th>
                        <th class="{{ $thClass }}">Subscription</th>
                        <th class="{{ $thClass }}">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $statusLabel = $user->accountStatusLabel ?? 'inactive';
                            $statusClass = $statusClasses[$user->accountStatusLabel] ?? 'is-inactive';
                            $sub         = $user->subscription ?? null;
                            $subCode     = $user->plan->code ?? null;
                        @endphp
                        <tr>
                            <td class="{{ $tdClass }}">
                                <span class="users-pill {{ $statusClass }}">
                                    {{ ucfirst($statusLabel) }}
                                </span>
                            </td>
                            <td class="{{ $tdClass }}">
                                <a class="orders-link"
                                   href="/auth/admin/users/{{ urlencode($user->uid) }}">
                                    {{ $user->email ?? '-' }}
                                </a>
                             </td>
                             <td class="{{ $tdClass }}">{{ $user->name ?? '-' }}</td>
                            <td class="{{ $tdClass }}">
                                @if ($sub && $subCode)
                                    <a class="orders-link"
                                       href="/auth/admin/users/subscription/{{ urlencode($sub->uid) }}">
                                        <span class="orders-code">{{ $subCode }}</span>
                                    </a>
                                @else
                                    None
                                @endif
                            </td>
                            <td class="{{ $tdClass }}">{{ isset($user->created_at) ? \Illuminate\Support\Carbon::parse($user->created_at)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="orders-empty">No users match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            @include('partials.shared.pagination', [
                'currentPage' => $currentPage,
                'lastPage'    => $lastPage,
                'label'       => 'Users pagination',
                'baseQuery'   => array_filter([
                    'q'                => $q ?: null,
                    'has_subscription' => $hasSubscription !== 'all' ? $hasSubscription : null,
                    'account_state'    => $accountState !== 'all' ? $accountState : null,
                ]),
            ])
        @endif
    </section>
@endsection
