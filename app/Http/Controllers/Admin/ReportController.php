<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Enums\ReferralStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Units\Models\UnitLedgerEntry;
use App\Support\AdminPermission;
use App\Support\CsvExporter;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(Request $request): View|StreamedResponse|Response
    {
        $this->requirePermission(AdminPermission::REPORTS);

        $from = $request->date('from')?->startOfDay() ?? now()->subDays(29)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        $summary = [
            'members_new' => User::query()->where('role', UserRole::Member)->whereBetween('created_at', [$from, $to])->count(),
            'members_active' => User::query()->where('role', UserRole::Member)->where('status', UserStatus::Active)->count(),
            'units_issued' => (int) UnitLedgerEntry::query()->whereBetween('created_at', [$from, $to])->where('units', '>', 0)->sum('units'),
            'revenue' => Money::present((string) Payment::query()->where('status', PaymentStatus::Successful)->whereBetween('paid_at', [$from, $to])->sum('amount_usd')),
            'referrals_rewarded' => Referral::query()->where('status', ReferralStatus::Rewarded)->whereBetween('rewarded_at', [$from, $to])->count(),
            'referral_units' => (int) UnitLedgerEntry::query()->where('type', LedgerType::ReferralBonus)->whereBetween('created_at', [$from, $to])->sum('units'),
        ];

        if ($request->query('export') === 'csv') {
            return CsvExporter::download('report.csv', ['Metric', 'Value'], collect($summary)->map(fn ($value, $key) => [$key, $value]));
        }

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', [
                'summary' => $summary,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'siteName' => setting('site_name', 'minimini.org'),
            ]);

            return $pdf->download('report-'.$from->toDateString().'-'.$to->toDateString().'.pdf');
        }

        return view('admin.reports.index', [
            'summary' => $summary,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }
}
