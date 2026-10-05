<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StopImpersonation
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('admin.impersonate.stop')) {
            return $next($request);
        }

        $adminId = $request->session()->pull('impersonator_id');

        if ($adminId === null) {
            return redirect()->route('admin.dashboard');
        }

        $admin = User::query()->find($adminId);

        if ($admin === null || ! $admin->isAdmin()) {
            Auth::logout();

            return redirect()->route('login');
        }

        $was = $request->user();
        Auth::login($admin);
        $request->session()->forget('impersonator_id');

        app(AuditLogService::class)->record(
            $admin,
            'admin.impersonation.stopped',
            $was,
            null,
            ['returned_to' => $admin->id],
            $request,
        );

        return redirect()->route('admin.members.show', $was ?? $admin);
    }
}
