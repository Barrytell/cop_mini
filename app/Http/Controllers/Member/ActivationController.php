<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\UnitBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function __invoke(Request $request, UnitBalanceService $balances): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->status === UserStatus::Active) {
            return redirect()->route('member.units.buy');
        }

        return view('member.activate', $this->checkout($request, $balances));
    }

    /**
     * @return array<string, mixed>
     */
    public static function checkout(Request $request, UnitBalanceService $balances): array
    {
        $currencies = config('services.flutterwave.currencies');

        return [
            'balance' => $balances->balance($request->user()),
            'unitPrice' => (string) setting('unit_price_usd', '0.01'),
            'minimum' => (string) setting('min_payment_usd', '10.00'),
            'currencies' => is_array($currencies) ? $currencies : [],
            'payments' => $request->user()->payments()->latest()->limit(5)->get(),
        ];
    }
}
