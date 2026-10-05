<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferralCode
{
    public function handle(Request $request, Closure $next): Response
    {
        $ref = $request->query('ref');

        if (! is_string($ref) || str_starts_with($request->path(), 'webhooks') || ! (bool) setting('referral_program_enabled', true)) {
            return $next($request);
        }

        $code = strtoupper(trim($ref));

        if ($code === '' || strlen($code) > 16) {
            return $next($request);
        }

        $referrer = User::query()->where('referral_code', $code)->first();

        if ($referrer === null || $request->user()?->id === $referrer->id) {
            return $next($request);
        }

        $request->session()->put('referral_code', $code);
        cookie()->queue(cookie('referral_code', $code, 60 * 24 * 30));

        return $next($request);
    }
}
