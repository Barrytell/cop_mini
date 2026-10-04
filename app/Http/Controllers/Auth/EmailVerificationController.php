<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\RedirectsAuthenticatedUser;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->to(app(RedirectsAuthenticatedUser::class)->url($request->user()));
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request, RedirectsAuthenticatedUser $redirects): RedirectResponse
    {
        $request->fulfill();

        return redirect()->to($redirects->url($request->user()))->with('status', 'Email address confirmed.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->to(app(RedirectsAuthenticatedUser::class)->url($request->user()));
        }

        $request->user()?->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh verification link has been sent.');
    }
}
