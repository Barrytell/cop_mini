<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustUnitsRequest;
use App\Http\Requests\Admin\AdminActionReasonRequest;
use App\Http\Requests\Admin\UpdateMemberRequest;
use App\Models\User;
use App\Modules\Members\Actions\AdjustMemberUnits;
use App\Modules\Members\Actions\AssignMemberNumber;
use App\Services\AuditLogService;
use App\Services\UnitBalanceService;
use App\Support\AdminPermission;
use App\Support\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(Request $request): View|StreamedResponse
    {
        $this->requirePermission(AdminPermission::MEMBERS);

        $query = User::query()
            ->withTrashed()
            ->where('role', UserRole::Member)
            ->with('referrer')
            ->latest('id');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('member_no', 'like', '%'.$search.'%')
                    ->orWhere('referral_code', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($status = $request->query('status')) {
            if (is_string($status) && $status !== '') {
                $query->where('status', $status);
            }
        }

        if ($country = $request->query('country')) {
            if (is_string($country) && $country !== '') {
                $query->where('country', $country);
            }
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($request->query('export') === 'csv') {
            $rows = (clone $query)->limit(5000)->get()->map(fn (User $user) => [
                $user->member_no,
                $user->name,
                $user->email,
                $user->phone,
                $user->country,
                $user->status->value,
                $user->created_at?->toDateString(),
                $user->deleted_at ? 'deleted' : 'active_row',
            ]);

            return CsvExporter::download('members.csv', [
                'Member no', 'Name', 'Email', 'Phone', 'Country', 'Status', 'Joined', 'Row',
            ], $rows);
        }

        return view('admin.members.index', [
            'members' => $query->paginate(20)->withQueryString(),
            'countries' => User::query()->where('role', UserRole::Member)->distinct()->orderBy('country')->pluck('country'),
            'filters' => $request->only(['q', 'status', 'country', 'from', 'to']),
        ]);
    }

    public function show(User $member, UnitBalanceService $balances): View
    {
        $this->authorize('view', $member);
        abort_unless($member->role === UserRole::Member, 404);

        $member->load(['referrer', 'referralReceived.referrer']);

        return view('admin.members.show', [
            'member' => $member,
            'balance' => $balances->balance($member),
            'payments' => $member->payments()->latest('id')->paginate(10, ['*'], 'payments_page'),
            'ledger' => $member->ledgerEntries()->latest('id')->paginate(15, ['*'], 'ledger_page'),
            'referrals' => $member->referralsMade()->with('referred')->latest('id')->paginate(10, ['*'], 'referrals_page'),
        ]);
    }

    public function edit(User $member): View
    {
        $this->authorize('update', $member);
        abort_unless($member->role === UserRole::Member, 404);

        return view('admin.members.edit', [
            'member' => $member,
            'countries' => config('countries'),
        ]);
    }

    public function update(UpdateMemberRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        abort_unless($member->role === UserRole::Member, 404);

        $previous = $member->only(['name', 'email', 'phone', 'country', 'status']);
        $member->fill($request->safe()->only(['name', 'email', 'phone', 'country', 'status']));
        $member->save();

        $audit->record($request->user(), 'admin.member.updated', $member, $previous, $member->only(['name', 'email', 'phone', 'country', 'status']), $request);

        return redirect()->route('admin.members.show', $member)->with('status', 'Member updated.');
    }

    public function activate(AdminActionReasonRequest $request, User $member, AssignMemberNumber $assignMemberNumber, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('activate', $member);

        DB::transaction(function () use ($member, $assignMemberNumber): void {
            $locked = User::query()->whereKey($member->id)->lockForUpdate()->firstOrFail();
            $locked->forceFill(['status' => UserStatus::Active])->save();
            $assignMemberNumber->handle($locked);
        });

        $audit->record($request->user(), 'admin.member.activated', $member, ['status' => $member->status->value], [
            'status' => UserStatus::Active->value,
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Member activated.');
    }

    public function suspend(AdminActionReasonRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('suspend', $member);
        $previous = $member->status->value;
        $member->forceFill(['status' => UserStatus::Suspended])->save();
        $audit->record($request->user(), 'admin.member.suspended', $member, ['status' => $previous], [
            'status' => UserStatus::Suspended->value,
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Member suspended.');
    }

    public function unsuspend(AdminActionReasonRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('suspend', $member);
        $previous = $member->status->value;
        $member->forceFill(['status' => UserStatus::Active])->save();
        $audit->record($request->user(), 'admin.member.unsuspended', $member, ['status' => $previous], [
            'status' => UserStatus::Active->value,
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Member unsuspended.');
    }

    public function resetPassword(AdminActionReasonRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('resetPassword', $member);
        $password = Str::password(12);
        $member->forceFill(['password' => Hash::make($password)])->save();
        $audit->record($request->user(), 'admin.member.password_reset', $member, null, [
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Temporary password: '.$password);
    }

    public function destroy(AdminActionReasonRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('delete', $member);
        $member->delete();
        $audit->record($request->user(), 'admin.member.deleted', $member, null, [
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return redirect()->route('admin.members.index')->with('status', 'Member soft-deleted.');
    }

    public function restore(AdminActionReasonRequest $request, User $member, AuditLogService $audit): RedirectResponse
    {
        abort_unless($member->role === UserRole::Member, 404);
        $this->authorize('restore', $member);
        $member->restore();
        $audit->record($request->user(), 'admin.member.restored', $member, null, [
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return redirect()->route('admin.members.show', $member)->with('status', 'Member restored.');
    }

    public function adjustUnits(AdjustUnitsRequest $request, User $member, AdjustMemberUnits $adjust, AuditLogService $audit): RedirectResponse
    {
        try {
            $entry = $adjust->handle(
                $member,
                (int) $request->integer('units'),
                $request->string('note')->toString(),
                $request->user(),
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['units' => $exception->getMessage()]);
        }

        $audit->record($request->user(), 'admin.member.units_adjusted', $member, null, [
            'units' => $entry->units,
            'note' => $entry->note,
            'ledger_id' => $entry->id,
        ], $request);

        return back()->with('status', 'Units adjusted.');
    }

    public function impersonate(Request $request, User $member, AuditLogService $audit): RedirectResponse
    {
        $this->authorize('impersonate', $member);
        $admin = $request->user();
        $request->session()->put('impersonator_id', $admin->id);
        auth()->login($member);
        $request->session()->regenerate();
        $audit->record($admin, 'admin.impersonation.started', $member, null, ['member_id' => $member->id], $request);

        return redirect()->route('member.dashboard')->with('status', 'You are viewing this account as an administrator.');
    }
}
