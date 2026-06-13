<?php

namespace App\Services\Admin;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Build paginated users list for the admin users table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $hasSubscription = strtolower(trim((string) $request->query('has_subscription', 'all')));
        $accountState = strtolower(trim((string) $request->query('account_state', 'all')));
        $search = trim((string) $request->query('q', ''));

        $query = User::query()
            ->latest('id');

        if ($hasSubscription === 'yes') {
            $query->has('subscription');
        } elseif ($hasSubscription === 'no') {
            $query->doesntHave('subscription');
        }

        if ($accountState !== 'all') {
            $stateValue = match ($accountState) {
                'inactive' => AccountStatus::INACTIVE->value,
                'verified' => AccountStatus::VERIFIED->value,
                'suspended' => AccountStatus::SUSPENDED->value,
                'deactivated' => AccountStatus::DEACTIVATED->value,
                default => null,
            };

            if ($stateValue !== null) {
                $query->where('account_status', $stateValue);
            }
        }

        if ($search !== '') {
            $query->where('email', 'like', '%'.$search.'%');
        }

        return $query->paginate($limit)->appends($request->query());
    }

    /**
     * Find a user by UID or throw a 404 error.
     */
    public function findById(string $userId): User
    {
        return User::query()->where('uid', $userId)->firstOrFail();
    }

    /**
     * Format user record for table row rendering.
     */
    public function mapUser(User $user): array
    {
        $subscription = $user->subscription;

        return [
            'uid' => $user->uid,
            'name' => $user->name,
            'email' => $user->email,
            'account_status' => [
                'value' => $user->account_status?->value,
                'label' => strtolower($user->account_status?->name ?? 'inactive'),
            ],
            'subscription' => $subscription ? [
                'uid' => $subscription->uid,
                'status' => $subscription->status,
                'plan' => [
                    'uid' => $subscription->plan?->uid,
                    'code' => $subscription->plan?->code,
                    'quota' => $subscription->plan?->quota,
                ],
                'expiration_date' => $subscription->expiration_date,
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }   
}
