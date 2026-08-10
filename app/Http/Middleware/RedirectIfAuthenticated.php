<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;
        $isAdminRequest = $request->is('admin') || $request->is('admin/*');

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $isAdminRequest
                    ? redirect()->route('admin.stats')
                    : redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
