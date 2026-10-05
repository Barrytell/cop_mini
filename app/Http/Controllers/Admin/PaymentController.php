<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminActionReasonRequest;
use App\Modules\Payments\Actions\ManuallyConfirmPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\PaymentRefund;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use App\Support\CsvExporter;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(Request $request): View|StreamedResponse
    {
        $this->requirePermission(AdminPermission::PAYMENTS);

        $query = Payment::query()->with('user')->latest('id');

        if ($status = $request->query('status')) {
            if (is_string($status) && $status !== '') {
                $query->where('status', $status);
            }
        }

        if ($type = $request->query('type')) {
            if (is_string($type) && $type !== '') {
                $query->where('type', $type);
            }
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($min = $request->query('min')) {
            if (is_numeric($min)) {
                $query->where('amount_usd', '>=', $min);
            }
        }

        if ($max = $request->query('max')) {
            if (is_numeric($max)) {
                $query->where('amount_usd', '<=', $max);
            }
        }

        if ($request->query('export') === 'csv') {
            $rows = (clone $query)->limit(5000)->get()->map(fn (Payment $payment) => [
                $payment->tx_ref,
                $payment->user?->email,
                $payment->amount_usd,
                $payment->units_purchased,
                $payment->unit_price_snapshot,
                $payment->type->value,
                $payment->status->value,
                $payment->paid_at?->toDateTimeString(),
            ]);

            return CsvExporter::download('payments.csv', [
                'Reference', 'Member', 'Amount USD', 'Units', 'Unit price', 'Type', 'Status', 'Paid at',
            ], $rows);
        }

        return view('admin.payments.index', [
            'payments' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['status', 'type', 'from', 'to', 'min', 'max']),
            'revenue' => Money::present((string) Payment::query()->where('status', PaymentStatus::Successful)->sum('amount_usd')),
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->requirePermission(AdminPermission::PAYMENTS);
        $payment->load('user');

        return view('admin.payments.show', [
            'payment' => $payment,
            'refunds' => PaymentRefund::query()->where('payment_id', $payment->id)->latest('id')->get(),
        ]);
    }

    public function reverify(Request $request, Payment $payment, PaymentGatewayInterface $gateway, \App\Modules\Payments\Actions\ConfirmPayment $confirm, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::PAYMENTS);

        try {
            $verification = $payment->flw_transaction_id
                ? $gateway->verify((string) $payment->flw_transaction_id)
                : $gateway->verifyByReference($payment->tx_ref);
            $confirm->handle($payment, $verification);
        } catch (PaymentNotFoundException|PaymentGatewayException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        $audit->record($request->user(), 'admin.payment.reverified', $payment, null, [
            'tx_ref' => $payment->tx_ref,
            'status' => $payment->fresh()?->status->value,
        ], $request);

        return back()->with('status', 'Payment re-checked with Flutterwave.');
    }

    public function markSuccessful(AdminActionReasonRequest $request, Payment $payment, ManuallyConfirmPayment $manual, AuditLogService $audit): RedirectResponse
    {
        abort_unless($request->user()?->role === UserRole::SuperAdmin, 403);
        $this->requirePermission(AdminPermission::PAYMENTS);

        if ($payment->status === PaymentStatus::Successful) {
            return back()->with('status', 'Payment is already successful.');
        }

        $manual->handle($payment, $request->string('reason')->toString());
        $audit->record($request->user(), 'admin.payment.manual_success', $payment, ['status' => $payment->status->value], [
            'status' => PaymentStatus::Successful->value,
            'reason' => $request->string('reason')->toString(),
        ], $request);

        return back()->with('status', 'Payment marked successful and units credited.');
    }

    public function refund(AdminActionReasonRequest $request, Payment $payment, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::PAYMENTS);
        abort_unless($payment->status === PaymentStatus::Successful, 422);

        $refund = PaymentRefund::query()->create([
            'payment_id' => $payment->id,
            'amount_usd' => $payment->amount_usd,
            'reason' => $request->string('reason')->toString(),
            'created_by' => $request->user()->id,
            'meta' => ['logged_only' => true],
        ]);

        $audit->record($request->user(), 'admin.payment.refund_logged', $payment, null, [
            'refund_id' => $refund->id,
            'amount_usd' => (string) $refund->amount_usd,
            'reason' => $refund->reason,
        ], $request);

        return back()->with('status', 'Refund recorded in the log. Process the provider refund separately.');
    }
}
