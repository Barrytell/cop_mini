<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Modules\Members\Actions\RegisterMember;
use App\Support\RedirectsAuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless((bool) setting('registration_open', true), 403, 'Registration is closed.');

        $code = strtoupper(trim((string) $request->query('ref', '')));

        if ($code !== '' && User::query()->where('referral_code', $code)->exists()) {
            $request->session()->put('referral_code', $code);
            cookie()->queue(cookie('referral_code', $code, 60 * 24 * 30));
        }

        $referralCode = strtoupper(trim((string) $request->session()->get(
            'referral_code',
            $request->cookie('referral_code', ''),
        )));
        $referrer = $referralCode !== ''
            ? User::query()->where('referral_code', $referralCode)->first()
            : null;

        return view('auth.register', [
            'countries' => config('countries'),
            'referralCode' => $referrer?->referral_code ?? ($referralCode !== '' ? $referralCode : null),
            'referrerName' => $referrer?->name,
        ]);
    }

    public function store(RegisterRequest $request, RegisterMember $registerMember, RedirectsAuthenticatedUser $redirects): RedirectResponse
    {
        abort_unless((bool) setting('registration_open', true), 403, 'Registration is closed.');

        $user = $registerMember->handle([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'country' => $request->string('country')->toString(),
            'password' => $request->string('password')->toString(),
            'referral_code' => $request->referralCode(),
        ]);

        $request->session()->forget('referral_code');
        cookie()->queue(cookie()->forget('referral_code'));

        return redirect()->to($redirects->url($user));
    }
}
