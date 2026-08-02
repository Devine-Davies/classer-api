<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictCheckoutAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('classer.checkout_access.enabled', false)) {
            return $next($request);
        }

        if ($request->session()->get('checkout_access_granted') === true) {
            return $next($request);
        }

        $accessKey = trim((string) config('classer.checkout_access.key', ''));
        $queryParam = (string) config('classer.checkout_access.query_param', 'access');
        $providedKey = (string) $request->query($queryParam, '');

        if ($accessKey !== '' && hash_equals($accessKey, $providedKey)) {
            $request->session()->put('checkout_access_granted', true);

            return $next($request);
        }

        abort(404);
    }
}