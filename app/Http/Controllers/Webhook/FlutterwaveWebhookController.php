<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlutterwaveWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentGatewayInterface $gateway,
        ConfirmPayment $confirmPayment,
    ): JsonResponse {
        $secretHash = (string) config('services.flutterwave.webhook_hash');
        $header = (string) $request->header('verif-hash', '');

        if ($secretHash === '' || $header === '' || ! hash_equals($secretHash, $header)) {
            abort(401);
        }

        $event = (string) $request->input('event', 'charge.completed');

        if (! in_array($event, ['charge.completed', 'charge.failed'], true)) {
            return response()->json(['message' => 'ignored']);
        }

        $transactionId = (string) data_get($request->all(), 'data.id', '');
        $txRef = (string) data_get($request->all(), 'data.tx_ref', '');

        if ($transactionId === '' && $txRef === '') {
            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        try {
            $verification = $transactionId !== ''
                ? $gateway->verify($transactionId)
                : $gateway->verifyByReference($txRef);
        } catch (PaymentNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], 202);
        } catch (PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $payment = Payment::query()->where('tx_ref', $verification->txRef)->first();

        if ($payment === null) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $confirmPayment->handle($payment, $verification);

        return response()->json(['message' => 'ok']);
    }
}
