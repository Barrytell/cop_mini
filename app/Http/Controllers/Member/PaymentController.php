<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\InitiatePaymentRequest;
use App\Modules\Payments\Actions\InitiatePayment;
use App\Modules\Payments\Actions\VerifyPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Models\Payment;
use App\Support\RedirectsAuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(InitiatePaymentRequest $request, InitiatePayment $initiatePayment): RedirectResponse
    {
        $url = $initiatePayment->handle(
            $request->user(),
            $request->string('amount_usd')->toString(),
        );

        return redirect()->away($url);
    }

    public function callback(
        Request $request,
        PaymentGatewayInterface $gateway,
        VerifyPayment $verifyPayment,
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

            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => 'The payment was cancelled.']);
        }

        if ($transactionId === '') {
            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => 'We could not match that payment.']);
        }

        try {
            $verification = $gateway->verify($transactionId);
            $payment = $verifyPayment->handle($payment, $verification);
        } catch (PaymentGatewayException $exception) {
            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => $exception->getMessage()]);
        }

        $request->user()->refresh();

        if ($payment->status === PaymentStatus::Cancelled) {
            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => 'The payment was cancelled.']);
        }

        if ($payment->status !== PaymentStatus::Successful) {
            return redirect()
                ->route('member.activate')
                ->withErrors(['amount_usd' => 'The payment was not confirmed.']);
        }

        return redirect()
            ->to($redirects->url($request->user()))
            ->with('status', 'Payment confirmed. Units have been added to your account.');
    }
}
