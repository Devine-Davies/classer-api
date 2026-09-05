<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Has
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        $user = $request->user();

        foreach ($types as $type) {
            if ($type === 'subscription') {
                if (! $this->hasSubscription($user)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have an active subscription.',
                    ], 403);
                }
            }

            if ($type === 'cloudShare') {
                if (! $this->hasCloudShare($user)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have access to Cloud Share.',
                    ], 403);
                }
            }

            if ($type === 'cloudBackup') {
                if (! $this->hasCloudBackup($user)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have access to Cloud Backup.',
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the user has the required subscription type.
     *
     * @param  string  $type
     */
    protected function hasSubscription(User $user): bool
    {
        return (bool) $user->subscription;
    }

    protected function hasCloudShare(User $user): bool
    {
        return $user->subscription?->plan?->hasEntitlement('cloud_share') ?? false;
    }

    protected function hasCloudBackup(User $user): bool
    {
        return $user->subscription?->plan?->hasEntitlement('cloud_backup') ?? false;
    }
}
