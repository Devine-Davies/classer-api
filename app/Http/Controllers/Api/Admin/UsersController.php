<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\CloudShare;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Admin\UserTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(private readonly UserTableService $userTableService) {}

    /**
     * List users with pagination, filters, and email search.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userTableService->paginate($request);

        return response()->json([
            'status' => true,
            'data' => $users->getCollection()->map(fn ($user) => $this->userTableService->mapUser($user))->values(),
            'filters' => [
                'has_subscription' => strtolower(trim((string) $request->query('has_subscription', 'all'))),
                'account_state' => strtolower(trim((string) $request->query('account_state', 'all'))),
                'q' => trim((string) $request->query('q', '')),
            ],
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    /**
     * Return user subscription details by UID.
     */
    public function showSubscription(string $subscriptionUid): JsonResponse
    {
        $subscription = UserSubscription::with('plan')
            ->where('uid', $subscriptionUid)
            ->firstOrFail();

        $user = User::query()
            ->select(['uid', 'name', 'email', 'account_status'])
            ->where('uid', $subscription->user_id)
            ->first();

        return response()->json([
            'status' => true,
            'data' => new SubscriptionResource($subscription),
            'user' => $user ? [
                'uid' => $user->uid,
                'name' => $user->name,
                'email' => $user->email,
                'account_status' => strtolower($user->account_status?->name ?? 'inactive'),
            ] : null,
        ]);
    }

    /**
     * Return a complete admin overview for one user.
     */
    public function show(string $userUid): JsonResponse
    {
        $user = User::query()
            ->with('cloudUsage')
            ->where('uid', $userUid)
            ->firstOrFail();

        $activeSubscriptionUid = $user->subscription?->uid;

        $subscriptions = UserSubscription::query()
            ->with('plan')
            ->where('user_id', $user->uid)
            ->latest('id')
            ->get()
            ->map(function (UserSubscription $subscription) use ($activeSubscriptionUid) {
                return [
                    'uid' => $subscription->uid,
                    'status' => $subscription->status,
                    'is_active' => $activeSubscriptionUid
                        ? $subscription->uid === $activeSubscriptionUid
                        : strtolower((string) $subscription->status) === 'active',
                    'plan_id' => $subscription->plan_id,
                    'plan' => [
                        'uid' => $subscription->plan?->uid,
                        'title' => $subscription->plan?->title,
                        'code' => $subscription->plan?->code,
                        'quota' => $subscription->plan?->quota,
                    ],
                    'auto_renew' => (bool) $subscription->auto_renew,
                    'auto_renew_date' => $subscription->auto_renew_date,
                    'expiration_date' => $subscription->expiration_date,
                    'cancellation_date' => $subscription->cancellation_date,
                    'cancellation_reason' => $subscription->cancellation_reason,
                    'transaction_id' => $subscription->transaction_id,
                    'notes' => $subscription->notes,
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at,
                ];
            })
            ->values();

        $cloudShares = CloudShare::withTrashed()
            ->where('user_id', $user->uid)
            ->latest('id')
            ->get()
            ->map(function (CloudShare $share) {
                return [
                    'uid' => $share->uid,
                    'resource_id' => $share->resource_id,
                    'size' => (int) ($share->size ?? 0),
                    'created_at' => $share->created_at,
                    'updated_at' => $share->updated_at,
                    'deleted_at' => $share->deleted_at,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => [
                'user' => [
                    'uid' => $user->uid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_status' => [
                        'value' => $user->account_status?->value,
                        'label' => strtolower($user->account_status?->name ?? 'inactive'),
                    ],
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'cloud_usage' => [
                    'uid' => $user->cloudUsage?->uid,
                    'total_usage' => (int) ($user->cloudUsage?->total_usage ?? 0),
                    'updated_at' => $user->cloudUsage?->updated_at,
                ],
                'subscriptions' => $subscriptions,
                'active_subscription_uid' => $activeSubscriptionUid,
                'cloud_shares' => $cloudShares,
            ],
        ]);
    }

    /**
     * Return cloud share details with related cloud entities.
     */
    public function showCloudShare(string $cloudShareUid): JsonResponse
    {
        $cloudShare = CloudShare::withTrashed()
            ->with('cloudEntities')
            ->where('uid', $cloudShareUid)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'uid' => $cloudShare->uid,
                'user_id' => $cloudShare->user_id,
                'resource_id' => $cloudShare->resource_id,
                'size' => (int) ($cloudShare->size ?? 0),
                'created_at' => $cloudShare->created_at,
                'updated_at' => $cloudShare->updated_at,
                'deleted_at' => $cloudShare->deleted_at,
                'cloud_entities' => $cloudShare->cloudEntities
                    ->sortByDesc('id')
                    ->values()
                    ->map(function ($entity) {
                        return [
                            'uid' => $entity->uid,
                            'plan' => $entity->plan,
                            'status' => $entity->status,
                            'size' => (int) ($entity->size ?? 0),
                            'e_tag' => $entity->e_tag,
                            'key' => $entity->key,
                            'public_url' => $entity->public_url,
                            'expires_at' => $entity->expires_at,
                            'created_at' => $entity->created_at,
                            'updated_at' => $entity->updated_at,
                            'deleted_at' => $entity->deleted_at,
                        ];
                    }),
            ],
        ]);
    }
}
