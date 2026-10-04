<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = $request->user()?->status;

        if ($status === UserStatus::Pending) {
            return redirect()->route('member.activate');
        }

        if ($status === UserStatus::Suspended) {
            return redirect()->route('account.suspended');
        }

        if ($status !== UserStatus::Active) {
            abort(403);
        }

        return $next($request);
    }
}
