<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTotpVerified
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin() || $user->totp_confirmed_at === null) {
            return $next($request);
        }

        if ($request->session()->get('totp_passed') === $user->id) {
            return $next($request);
        }

        if ($request->routeIs('admin.totp.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('admin.totp.challenge');
    }
}
