<?php

declare(strict_types=1);

namespace App\Modules\Payments\Gateways;

use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Data\PaymentInitialization;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\Http;
use JsonException;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    public function initialize(Payment $payment, User $user, string $redirectUrl): PaymentInitialization
    {
        $secret = (string) config('flutterwave.secret_key');

        if ($secret === '') {
            throw new PaymentGatewayException('Flutterwave is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->timeout(20)
            ->post(config('flutterwave.base_url').'/v3/payments', [
                'tx_ref' => $payment->tx_ref,
                'amount' => (string) $payment->amount_usd,
                'currency' => 'USD',
                'redirect_url' => $redirectUrl,
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'phonenumber' => $user->phone,
                ],
                'customizations' => [
                    'title' => (string) setting('site_name', 'minimini.org'),
                    'description' => 'Unit purchase '.$payment->tx_ref,
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
                ],
            ]);

        $payload = $response->json();

        if (! $response->successful() || ($payload['status'] ?? null) !== 'success' || empty($payload['data']['link'])) {
            throw new PaymentGatewayException('Flutterwave could not start this payment.');
        }

        return new PaymentInitialization(
            (string) $payload['data']['link'],
            is_array($payload) ? $payload : [],
        );
    }

    public function verify(string $transactionId): PaymentVerification
    {
        if (! preg_match('/^\d+$/', $transactionId)) {
            throw new PaymentGatewayException('The transaction id is invalid.');
        }

        $secret = (string) config('flutterwave.secret_key');

        if ($secret === '') {
            throw new PaymentGatewayException('Flutterwave is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->timeout(20)
            ->get(config('flutterwave.base_url').'/v3/transactions/'.$transactionId.'/verify');

        $body = $response->body();

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new PaymentGatewayException('Flutterwave returned an unreadable verification response.');
        }

        $data = $payload['data'] ?? null;

        if (! $response->successful() || ! is_array($data)) {
            throw new PaymentGatewayException('Flutterwave could not verify this payment.');
        }

        return new PaymentVerification(
            successful: ($data['status'] ?? null) === 'successful',
            transactionId: (string) ($data['id'] ?? $transactionId),
            txRef: (string) ($data['tx_ref'] ?? ''),
            amount: $this->amountFromBody($body),
            currency: strtoupper((string) ($data['currency'] ?? '')),
            rawBody: $body,
            gatewayStatus: strtolower((string) ($data['status'] ?? '')),
        );
    }

    /**
     * Pull the transaction amount out of the raw JSON so a float never enters the money path.
     * The amount that sits beside "currency" inside "data" is the charged amount. An earlier
     * "amount" key (meta, for example) must not be used.
     */
    private function amountFromBody(string $body): string
    {
        $subject = $body;

        if (preg_match('/"data"\s*:\s*(\{.*\})/s', $body, $dataMatch) === 1) {
            $subject = $dataMatch[1];
        }

        $matched = preg_match('/"amount"\s*:\s*"?(?<amount>\d+(?:\.\d+)?)"?\s*,\s*"currency"/', $subject, $matches) === 1
            || preg_match('/"amount"\s*:\s*"?(?<amount>\d+(?:\.\d+)?)"?/', $subject, $matches) === 1;

        if (! $matched) {
            throw new PaymentGatewayException('Verification response did not include an amount.');
        }

        return Money::normalize($matches['amount'], 2);
    }
}
