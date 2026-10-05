<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LedgerType;
use App\Enums\ReferralStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminActionReasonRequest;
use App\Models\User;
use App\Modules\Referrals\Actions\RewardReferral;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Units\Actions\AppendLedgerEntry;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReferralController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(Request $request): View
    {
        $this->requirePermission(AdminPermission::REFERRALS);

        $query = Referral::query()->with(['referrer', 'referred'])->latest('id');

        if ($status = $request->query('status')) {
            if (is_string($status) && $status !== '') {
                $query->where('status', $status);
            }
        }

        $leaderboard = Referral::query()
            ->selectRaw('referrer_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rewarded_count, SUM(bonus_units) as bonus_units', [ReferralStatus::Rewarded->value])
            ->groupBy('referrer_id')
            ->orderByDesc('rewarded_count')
            ->limit(20)
            ->with('referrer')
            ->get();

        return view('admin.referrals.index', [
            'referrals' => $query->paginate(25)->withQueryString(),
            'leaderboard' => $leaderboard,
            'filters' => $request->only(['status']),
        ]);
    }

    public function reward(AdminActionReasonRequest $request, Referral $referral, RewardReferral $reward, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::REFERRALS);
        $referred = User::withTrashed()->findOrFail($referral->referred_id);
        $reward->handle($referred);
        $audit->record($request->user(), 'admin.referral.manual_reward', $referral, null, [
            'reason' => $request->string('reason')->toString(),
            'status' => $referral->fresh()?->status->value,
        ], $request);

        return back()->with('status', 'Referral reward processed.');
    }

    public function reverse(AdminActionReasonRequest $request, Referral $referral, AppendLedgerEntry $ledger, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::REFERRALS);
        abort_unless($referral->status === ReferralStatus::Rewarded && $referral->bonus_units > 0, 422);

        DB::transaction(function () use ($referral, $ledger, $request): void {
            $locked = Referral::query()->whereKey($referral->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === ReferralStatus::Rewarded, 422);

            $referrer = User::withTrashed()->whereKey($locked->referrer_id)->lockForUpdate()->firstOrFail();
            $ledger->handle(
                user: $referrer,
                units: -1 * (int) $locked->bonus_units,
                type: LedgerType::AdminAdjustment,
                reference: $locked,
                note: 'Referral bonus reversed: '.$request->string('reason')->toString(),
                actor: $request->user(),
            );

            $locked->forceFill([
                'status' => ReferralStatus::Pending,
                'bonus_units' => 0,
                'rewarded_at' => null,
            ])->save();
        });

        $audit->record($request->user(), 'admin.referral.reversed', $referral, null, [
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Referral bonus reversed.');
    }
}
