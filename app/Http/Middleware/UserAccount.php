<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Logging\AppLogger;

class UserAccount
{
    /**
     * Constructor for the UserAccount command.
     * @param \App\Logging\AppLogger $logger
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext(context: 'UserAccount');
    }

    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();

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

            $request->attributes->add(['user' => $user]);
            return $next($request);
        } catch (\Throwable $th) {
            $this->logger->error("Error getting user account", [
                'error' => $th->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request',
            ], 500);
        }
    }
}
