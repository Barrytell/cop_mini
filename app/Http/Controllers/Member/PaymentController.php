<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\InitiatePaymentRequest;
use App\Http\Requests\Member\QuotePaymentRequest;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Actions\InitiatePayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;
use App\Support\RedirectsAuthenticatedUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        return view('member.payments.index', [
            'payments' => $request->user()->payments()->latest()->paginate(15),
        ]);
    }

    public function quote(QuotePaymentRequest $request, PaymentGatewayInterface $gateway): JsonResponse
    {
        try {
            $normalized = Money::normalize($request->string('amount_usd')->toString(), 2);
            $currency = $request->string('currency')->toString();
            $price = Money::normalize((string) setting('unit_price_usd', '0.01'), 6);
            $units = Money::unitsFrom($normalized, $price);
            $charge = $gateway->quote($normalized, $currency);
        } catch (\InvalidArgumentException|PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'units' => $units,
            'unit_price' => $price,
            'charge_amount' => $charge,
            'charge_currency' => $currency,
        ]);
    }

    public function store(InitiatePaymentRequest $request, InitiatePayment $initiatePayment): RedirectResponse
    {
        $url = $initiatePayment->handle(
            $request->user(),
            $request->string('amount_usd')->toString(),
            $request->string('currency')->toString(),
        );

        return redirect()->away($url);
    }

    public function callback(
        Request $request,
        PaymentGatewayInterface $gateway,
        ConfirmPayment $confirmPayment,
        RedirectsAuthenticatedUser $redirects,
    ): RedirectResponse {
        $transactionId = (string) $request->query('transaction_id', '');
        $txRef = (string) $request->query('tx_ref', '');

        $payment = Payment::query()
            ->where('tx_ref', $txRef)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($payment === null) {
            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => 'We could not match that payment.']);
        }

        if ($transactionId === '' && strtolower((string) $request->query('status')) === 'cancelled') {
            if ($payment->status === PaymentStatus::Pending) {
                $payment->forceFill(['status' => PaymentStatus::Cancelled])->save();
            }

            return $this->backToCheckout($request)->withErrors(['amount_usd' => 'The payment was cancelled.']);
        }

        if ($transactionId === '') {
            return $this->backToCheckout($request)->withErrors(['amount_usd' => 'We could not match that payment.']);
        }

        try {
            $verification = $gateway->verify($transactionId);
            $payment = $confirmPayment->handle($payment, $verification);
        } catch (PaymentGatewayException $exception) {
            return $this->backToCheckout($request)->withErrors(['amount_usd' => $exception->getMessage()]);
        }

        $request->user()->refresh();

        if ($payment->status === PaymentStatus::Cancelled) {
            return $this->backToCheckout($request)->withErrors(['amount_usd' => 'The payment was cancelled.']);
        }

        if ($payment->status !== PaymentStatus::Successful) {
            return $this->backToCheckout($request)->withErrors(['amount_usd' => 'The payment was not confirmed.']);
        }

        return redirect()
            ->to($redirects->url($request->user()))
            ->with('status', 'Payment confirmed. Units have been added to your account.');
    }

    public function receipt(Request $request, Payment $payment): Response
    {
        $this->authorize('view', $payment);

        abort_unless($payment->status === PaymentStatus::Successful, 404);
        abort_unless($payment->user_id === $request->user()->id || $request->user()->isAdmin(), 404);

        $payment->loadMissing('user');

        $pdf = Pdf::loadView('member.payments.receipt', [
            'payment' => $payment,
            'siteName' => (string) setting('site_name', 'minimini.org'),
        ]);

        return $pdf->download('receipt-'.$payment->tx_ref.'.pdf');
    }

    private function backToCheckout(Request $request): RedirectResponse
    {
        $route = $request->user()->status === UserStatus::Active
            ? 'member.units.buy'
            : 'member.activate';

        return redirect()->route($route);
    }
}
