<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWhenMaintenance
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) setting('maintenance_mode', false)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->isAdmin()) {
            return $next($request);
        }

        if ($request->is('admin*') || $request->is('login') || $request->is('logout') || $request->is('up')) {
            return $next($request);
        }

        abort(503);
    }
}
