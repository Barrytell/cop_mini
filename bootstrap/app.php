<?php

use App\Http\Middleware\CaptureReferralCode;
use App\Http\Middleware\EnsureAccountIsNotSuspended;
use App\Http\Middleware\EnsureMemberIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use App\Support\RedirectsAuthenticatedUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'account.not_suspended' => EnsureAccountIsNotSuspended::class,
            'member.active' => EnsureMemberIsActive::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            CaptureReferralCode::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/flutterwave',
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if ($user === null) {
                return route('home');
            }

            return app(RedirectsAuthenticatedUser::class)->url($user);
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
