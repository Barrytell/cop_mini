<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Support\Totp;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TotpController extends Controller
{
    public function setup(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);

        if ($user->totp_secret === null) {
            $user->forceFill(['totp_secret' => Totp::secret()])->save();
        }

        $url = Totp::otpAuthUrl($user->totp_secret, $user->email, (string) setting('site_name', 'minimini.org'));
        $qr = (new QRCode(new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'outputBase64' => false,
            'scale' => 5,
        ])))->render($url);

        return view('admin.totp.setup', [
            'secret' => $user->totp_secret,
            'qr' => $qr,
            'confirmed' => $user->totp_confirmed_at !== null,
        ]);
    }

    public function confirm(Request $request, AuditLogService $audit): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isAdmin() && $user->totp_secret, 403);

        $data = $request->validate(['code' => ['required', 'string']]);

        if (! Totp::verify($user->totp_secret, $data['code'])) {
            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        $user->forceFill(['totp_confirmed_at' => now()])->save();
        $request->session()->put('totp_passed', $user->id);
        $audit->record($user, 'admin.totp.confirmed', $user, null, ['enabled' => true], $request);

        return redirect()->route('admin.dashboard')->with('status', 'Two-factor authentication is on.');
    }

    public function challenge(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('admin.totp.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isAdmin() && $user->totp_confirmed_at, 403);

        $data = $request->validate(['code' => ['required', 'string']]);

        if (! Totp::verify((string) $user->totp_secret, $data['code'])) {
            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        $request->session()->put('totp_passed', $user->id);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function disable(Request $request, AuditLogService $audit): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);
        $data = $request->validate(['code' => ['required', 'string']]);

        if ($user->totp_secret && ! Totp::verify($user->totp_secret, $data['code'])) {
            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        $user->forceFill(['totp_secret' => null, 'totp_confirmed_at' => null])->save();
        $request->session()->forget('totp_passed');
        $audit->record($user, 'admin.totp.disabled', $user, null, ['enabled' => false], $request);

        return back()->with('status', 'Two-factor authentication is off.');
    }
}
