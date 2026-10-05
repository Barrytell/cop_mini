<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\LedgerType;
use App\Http\Controllers\Controller;
use App\Services\ReferralQr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $link = route('register', ['ref' => $user->referral_code]);

        return view('member.referrals.index', [
            'link' => $link,
            'shareText' => 'Join '.$this->siteName().' with my referral link.',
            'referrals' => $user->referralsMade()->with('referred')->latest()->paginate(15),
            'bonusUnits' => (int) $user->ledgerEntries()->where('type', LedgerType::ReferralBonus)->sum('units'),
        ]);
    }

    public function qr(Request $request, ReferralQr $qr): Response
    {
        $link = route('register', ['ref' => $request->user()->referral_code]);

        return response($qr->svg($link), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function siteName(): string
    {
        return (string) setting('site_name', 'minimini.org');
    }
}
