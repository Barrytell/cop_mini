<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\UnitBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function __invoke(Request $request, UnitBalanceService $balances): View
    {
        $user = $request->user();

        return view('member.activate', [
            'balance' => $balances->balance($user),
            'unitPrice' => (string) setting('unit_price_usd', '0.01'),
            'minimum' => (string) setting('min_payment_usd', '10.00'),
            'payments' => $user->payments()->latest()->limit(10)->get(),
        ]);
    }
}
