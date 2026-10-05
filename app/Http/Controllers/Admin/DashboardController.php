<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Units\Models\UnitLedgerEntry;
use App\Support\AdminPermission;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AuthorizesAdminPermission;

    public function __invoke(Request $request): View
    {
        $this->requirePermission(AdminPermission::DASHBOARD);

        $from = $request->date('from')?->startOfDay() ?? now()->subDays(29)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $successful = Payment::query()->where('status', PaymentStatus::Successful);

        $revenueToday = (clone $successful)->whereDate('paid_at', today())->sum('amount_usd');
        $revenueMonth = (clone $successful)->whereBetween('paid_at', [now()->startOfMonth(), now()])->sum('amount_usd');
        $revenueAll = (clone $successful)->sum('amount_usd');

        $signupSeries = User::query()
            ->where('role', UserRole::Member)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $revenueSeries = Payment::query()
            ->where('status', PaymentStatus::Successful)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('DATE(paid_at) as day, SUM(amount_usd) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $days[] = [
                'day' => $key,
                'signups' => (int) ($signupSeries[$key] ?? 0),
                'revenue' => Money::present((string) ($revenueSeries[$key] ?? '0')),
                'revenue_raw' => (string) ($revenueSeries[$key] ?? '0'),
            ];
        }

        return view('admin.dashboard', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totalMembers' => User::query()->where('role', UserRole::Member)->count(),
            'activeMembers' => User::query()->where('role', UserRole::Member)->where('status', UserStatus::Active)->count(),
            'pendingMembers' => User::query()->where('role', UserRole::Member)->where('status', UserStatus::Pending)->count(),
            'totalUnits' => (int) UnitLedgerEntry::query()->sum('units'),
            'referralBonuses' => (int) UnitLedgerEntry::query()->where('type', LedgerType::ReferralBonus)->sum('units'),
            'revenueToday' => Money::present((string) $revenueToday),
            'revenueMonth' => Money::present((string) $revenueMonth),
            'revenueAll' => Money::present((string) $revenueAll),
            'series' => $days,
            'recentPayments' => Payment::query()->with('user')->latest('id')->limit(8)->get(),
            'pendingTickets' => SupportTicket::query()->where('status', '!=', TicketStatus::Closed)->count(),
            'pendingPayments' => Payment::query()->where('status', PaymentStatus::Pending)->count(),
            'openReferrals' => Referral::query()->where('status', \App\Enums\ReferralStatus::Pending)->count(),
        ]);
    }
}
