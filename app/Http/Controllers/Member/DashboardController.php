<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\LedgerType;
use App\Enums\MeetingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Meetings\Models\Meeting;
use App\Services\UnitBalanceService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, UnitBalanceService $balances): View
    {
        $user = $request->user();
        $unitPrice = (string) setting('unit_price_usd', '0.01');
        $units = $balances->balance($user);
        $paid = '0.00';

        foreach ($user->payments()->where('status', PaymentStatus::Successful)->pluck('amount_usd') as $amount) {
            $paid = bcadd($paid, (string) $amount, 2);
        }

        $banners = Banner::query()->active()->where('position', 'member_dashboard')->orderBy('sort_order')->orderBy('id')->get();

        if ($banners->isEmpty()) {
            $banners = Banner::query()->active()->where('position', 'home_hero')->orderBy('sort_order')->orderBy('id')->get();
        }

        return view('member.dashboard', [
            'banners' => $banners,
            'units' => $units,
            'unitPrice' => $unitPrice,
            'unitPriceLabel' => Money::present($unitPrice),
            'contribution' => Money::contribution($units, $unitPrice),
            'totalPaid' => $paid,
            'referralCount' => $user->referralsMade()->count(),
            'bonusUnits' => (int) $user->ledgerEntries()->where('type', LedgerType::ReferralBonus)->sum('units'),
            'referralLink' => route('register', ['ref' => $user->referral_code]),
            'announcements' => Announcement::query()->published()->latest('published_at')->limit(3)->get(),
            'meetings' => Meeting::query()
                ->where('status', MeetingStatus::Scheduled)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),
            'ledger' => $user->ledgerEntries()->latest('id')->limit(8)->get(),
            'referrals' => $user->referralsMade()->with('referred')->latest()->limit(5)->get(),
        ]);
    }
}
