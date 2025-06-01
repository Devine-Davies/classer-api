<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Has
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        $user = $request->user();

        for ($i = 0; $i < count($types); $i++) {
            $type = $types[$i];
            if ($type === 'subscription') {
                if (!$this->hasSubscription($user)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have an active subscription.',
                    ], 403);
                }
            }

            if ($type === 'cloudStorage') {
                if (!$this->hasCloudStorage(
                    $user->subscription,
                    $user->cloudUsage
                )) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have access to cloud storage.',
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the user has the required subscription type.
     *
     * @param  \App\Models\User  $user
     * @param  string  $type
     * @return bool
     */
    protected function hasSubscription($user): bool
    {
        return !!$user->subscription;
    }

    /**
     * Check if the user has the required subscription type.
     *
     * @param  \App\Models\User  $user
     * @param  string  $type
     * @return boo
     */
    protected function hasCloudStorage($subscription, $cloudUsage): bool
    {
        return $subscription->tier->quota >= $cloudUsage->total;
    }
}
