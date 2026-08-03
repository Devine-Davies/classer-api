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

        $accessKey = trim((string) config('classer.checkout_access.key', ''));

        // If enabled without a key, deny all access.
        if ($accessKey === '') {
            return redirect('/');
        }

        $currentAccessSignature = hash('sha256', $accessKey);
        $grantedAccessSignature = (string) $request->session()->get('checkout_access_signature', '');

        if ($grantedAccessSignature !== '' && hash_equals($grantedAccessSignature, $currentAccessSignature)) {
            return $next($request);
        }

        $queryParam = (string) config('classer.checkout_access.query_param', 'access');
        $providedKey = (string) $request->query($queryParam, '');

        if ($accessKey !== '' && hash_equals($accessKey, $providedKey)) {
            $request->session()->put('checkout_access_signature', $currentAccessSignature);

            return $next($request);
        }

        // Clear stale session grants when key changed or no key is provided.
        $request->session()->forget('checkout_access_signature');

        return redirect('/');
    }
}