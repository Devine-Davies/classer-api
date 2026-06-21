<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $adminEmails = collect(
            explode(',', config('classer.admin_email', ''))
        )
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->values()
            ->all();

        $userEmail = strtolower(trim($user->email ?? ''));

        if (! in_array($userEmail, $adminEmails, true)) {
            abort(403, 'You are not allowed to access the admin area.');
        }

        return $next($request);
    }
}
