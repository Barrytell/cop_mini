<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Actions\ConfirmPayment;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\WebhookEvent;
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
        $valid = $secretHash !== '' && $header !== '' && hash_equals($secretHash, $header);
        $eventName = is_string($request->input('event')) ? $request->input('event') : null;
        $txRefHint = data_get($request->all(), 'data.tx_ref');
        $txRefHint = is_scalar($txRefHint) ? (string) $txRefHint : null;

        if (! $valid) {
            $this->log($request, $eventName, $txRefHint, 401, false);
            abort(401);
        }

        $event = $eventName ?: 'charge.completed';

        if (! in_array($event, ['charge.completed', 'charge.failed'], true)) {
            $this->log($request, $event, $txRefHint, 200, true);

            return response()->json(['message' => 'ignored']);
        }

        $transactionId = data_get($request->all(), 'data.id');
        $txRef = data_get($request->all(), 'data.tx_ref');
        $transactionId = is_scalar($transactionId) ? (string) $transactionId : '';
        $txRef = is_scalar($txRef) ? (string) $txRef : '';

        if ($transactionId === '' && $txRef === '') {
            $this->log($request, $event, null, 422, true);

            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        try {
            $verification = $transactionId !== ''
                ? $gateway->verify($transactionId)
                : $gateway->verifyByReference($txRef);
        } catch (PaymentNotFoundException $exception) {
            $this->log($request, $event, $txRef !== '' ? $txRef : $txRefHint, 202, true);

            return response()->json(['message' => $exception->getMessage()], 202);
        } catch (PaymentGatewayException $exception) {
            $this->log($request, $event, $txRef !== '' ? $txRef : $txRefHint, 422, true);

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $payment = Payment::query()->where('tx_ref', $verification->txRef)->first();

        if ($payment === null) {
            $this->log($request, $event, $verification->txRef, 200, true);

            return response()->json(['message' => 'ignored']);
        }

        $confirmPayment->handle($payment, $verification);
        $this->log($request, $event, $verification->txRef, 200, true);

        return response()->json(['message' => 'ok']);
    }

    private function log(Request $request, ?string $event, ?string $txRef, int $status, bool $valid): void
    {
        try {
            WebhookEvent::query()->create([
                'provider' => 'flutterwave',
                'event' => $event,
                'tx_ref' => $txRef,
                'http_status' => $status,
                'signature_valid' => $valid,
                'payload' => substr((string) $request->getContent(), 0, 65000),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Webhook processing must continue even if the log table is unavailable.
        }
    }
}
