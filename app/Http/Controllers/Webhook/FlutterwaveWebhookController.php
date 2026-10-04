<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Actions\VerifyPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlutterwaveWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentGatewayInterface $gateway,
        VerifyPayment $verifyPayment,
    ): JsonResponse {
        $secretHash = (string) config('flutterwave.secret_hash');
        $header = (string) $request->header('verif-hash', '');

        if ($secretHash === '' || $header === '' || ! hash_equals($secretHash, $header)) {
            abort(401);
        }

        $transactionId = (string) data_get($request->all(), 'data.id', '');

        if ($transactionId === '') {
            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        try {
            $verification = $gateway->verify($transactionId);
        } catch (PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $payment = Payment::query()->where('tx_ref', $verification->txRef)->first();

        if ($payment === null) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $verifyPayment->handle($payment, $verification);

        return response()->json(['message' => 'ok']);
    }
}
