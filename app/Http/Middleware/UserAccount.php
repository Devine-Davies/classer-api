<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccount
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()->load(['subscriptions' => function ($query) {
            $query->where('status', 1);
        }]);

        if ($user->accountInactive()) {
            return response()->json([
                'status' => false,
                'message' => 'Account need to be verified, please check your email',
            ], 401);
        }

        if ($user->accountDeactivated()) {
            return response()->json([
                'status' => false,
                'message' => 'Account has been deactivated, please contact support',
            ], 401);
        }

        if ($user->accountSuspended()) {
            return response()->json([
                'status' => false,
                'message' => 'Account has been suspended, please contact support',
            ], 401);
        }

        $request->attributes->add(['user' => $user]);
        return $next($request);
    }
}
