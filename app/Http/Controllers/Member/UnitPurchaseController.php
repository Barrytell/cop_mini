<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\UnitBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitPurchaseController extends Controller
{
    public function __invoke(Request $request, UnitBalanceService $balances): View
    {
        return view('member.buy', ActivationController::checkout($request, $balances));
    }
}
