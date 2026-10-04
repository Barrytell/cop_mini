<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\UnitBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, UnitBalanceService $balances): View
    {
        $user = $request->user();

        return view('member.dashboard', [
            'balance' => $balances->balance($user),
            'unitPrice' => (string) setting('unit_price_usd', '0.01'),
            'minimum' => (string) setting('min_payment_usd', '10.00'),
            'ledger' => $user->ledgerEntries()->latest()->limit(15)->get(),
            'referrals' => $user->referralsMade()->with('referred')->latest()->limit(10)->get(),
            'referralLink' => route('register', ['ref' => $user->referral_code]),
        ]);
    }
}
