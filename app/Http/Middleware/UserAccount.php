<?php

namespace App\Http\Middleware;

use App\Logging\AppLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAccount
{
    /**
     * Constructor for the UserAccount command.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'UserAccount');
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            if ($user->accountInactive()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Account has not been verified. Please check your email to verify your account',
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

            Auth::setUser($user);

            return $next($request);
        } catch (\Throwable $th) {
            $this->logger->error('Error getting user account', [
                'exception' => $th::class,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request',
            ], 500);
        }
    }
}
