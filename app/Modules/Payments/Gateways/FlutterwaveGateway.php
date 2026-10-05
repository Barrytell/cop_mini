<?php

declare(strict_types=1);

namespace App\Modules\Payments\Gateways;

use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Data\PaymentInitialization;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentGatewayTimeoutException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    public function initialize(Payment $payment, User $user, string $redirectUrl): PaymentInitialization
    {
        $currency = strtoupper((string) ($payment->charge_currency ?: 'USD'));
        $amount = (string) ($payment->charge_amount ?: $payment->amount_usd);

        $response = $this->send(fn (PendingRequest $http): Response => $http->post($this->baseUrl().'/v3/payments', [
            'tx_ref' => $payment->tx_ref,
            'amount' => $amount,
            'currency' => $currency,
            'redirect_url' => $redirectUrl,
            'payment_options' => $this->paymentOptions($currency),
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
        ]));

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

        $response = $this->send(fn (PendingRequest $http): Response => $http->get(
            $this->baseUrl().'/v3/transactions/'.$transactionId.'/verify'
        ));

        return $this->verificationFromResponse($response, $transactionId);
    }

    public function verifyByReference(string $txRef): PaymentVerification
    {
        if ($txRef === '') {
            throw new PaymentGatewayException('The transaction reference is missing.');
        }

        $response = $this->send(fn (PendingRequest $http): Response => $http->get(
            $this->baseUrl().'/v3/transactions/verify_by_reference',
            ['tx_ref' => $txRef],
        ));

        return $this->verificationFromResponse($response, '');
    }

    public function quote(string $amountUsd, string $currency): string
    {
        $amount = Money::normalize($amountUsd, 2);
        $currency = strtoupper($currency);

        if ($currency === 'USD') {
            return $amount;
        }

        if (! array_key_exists($currency, $this->currencies())) {
            throw new PaymentGatewayException('That payment currency is not supported.');
        }

        $response = $this->send(fn (PendingRequest $http): Response => $http->get($this->baseUrl().'/v3/transfers/rates', [
            'amount' => $amount,
            'source_currency' => 'USD',
            'destination_currency' => $currency,
        ]));

        $body = $response->body();

        if (! $response->successful()) {
            throw new PaymentGatewayException('Flutterwave could not price this currency.');
        }

        if (preg_match('/"destination"\s*:\s*\{[^}]*"amount"\s*:\s*"?(?<amount>\d+(?:\.\d+)?)"?/', $body, $matches) !== 1) {
            throw new PaymentGatewayException('Flutterwave did not return a converted amount.');
        }

        return Money::normalize($matches['amount'], 2);
    }

    public function paymentOptions(string $currency): string
    {
        $currency = strtoupper($currency);
        $options = $this->currencies()[$currency]['options'] ?? null;

        if (! is_string($options) || $options === '') {
            throw new PaymentGatewayException('That payment currency is not supported.');
        }

        return $options;
    }

    /**
     * @param  callable(PendingRequest): Response  $callback
     */
    private function send(callable $callback): Response
    {
        $secret = (string) config('services.flutterwave.secret_key');

        if ($secret === '') {
            throw new PaymentGatewayException('Flutterwave is not configured.');
        }

        try {
            return $callback(
                Http::withToken($secret)
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout(20)
            );
        } catch (ConnectionException) {
            throw new PaymentGatewayTimeoutException('Flutterwave did not respond in time. The payment stays pending and will be checked again.');
        }
    }

    private function verificationFromResponse(Response $response, string $fallbackId): PaymentVerification
    {
        $body = $response->body();

        if ($response->status() === 404) {
            throw new PaymentNotFoundException('No Flutterwave transaction for this reference yet.');
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new PaymentGatewayException('Flutterwave returned an unreadable verification response.');
        }

        $data = $payload['data'] ?? null;

        if (! $response->successful() || ! is_array($data)) {
            $message = strtolower((string) ($payload['message'] ?? ''));

            if (str_contains($message, 'not found') || str_contains($message, 'no transaction')) {
                throw new PaymentNotFoundException('No Flutterwave transaction for this reference yet.');
            }

            throw new PaymentGatewayException('Flutterwave could not verify this payment.');
        }

        return new PaymentVerification(
            successful: ($data['status'] ?? null) === 'successful',
            transactionId: (string) ($data['id'] ?? $fallbackId),
            txRef: (string) ($data['tx_ref'] ?? ''),
            amount: $this->amountFromBody($body),
            currency: strtoupper((string) ($data['currency'] ?? '')),
            rawBody: $body,
            gatewayStatus: strtolower((string) ($data['status'] ?? '')),
        );
    }

    /**
     * Pull data.amount out of the raw JSON so a float never enters the money path.
     * Nested objects are ignored, so a customer or meta amount cannot replace the charge.
     */
    private function amountFromBody(string $body): string
    {
        $data = $this->jsonObjectAfterKey($body, 'data') ?? $body;
        $amount = $this->topLevelJsonNumber($this->blankNested($data), 'amount');

        if ($amount === null) {
            throw new PaymentGatewayException('Verification response did not include an amount.');
        }

        return Money::normalize($amount, 2);
    }

    private function jsonObjectAfterKey(string $json, string $key): ?string
    {
        if (preg_match('/"'.preg_quote($key, '/').'"\s*:\s*\{/', $json, $matches, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        $start = $matches[0][1] + strlen($matches[0][0]) - 1;
        $length = strlen($json);
        $depth = 0;
        $inString = false;
        $escape = false;

        for ($i = $start; $i < $length; $i++) {
            $character = $json[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;

                    continue;
                }

                if ($character === '\\') {
                    $escape = true;

                    continue;
                }

                if ($character === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($character === '"') {
                $inString = true;

                continue;
            }

            if ($character === '{') {
                $depth++;

                continue;
            }

            if ($character === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($json, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    /**
     * Replace nested objects and arrays with spaces so a later search only sees top-level keys.
     */
    private function blankNested(string $json): string
    {
        $length = strlen($json);
        $depth = 0;
        $inString = false;
        $escape = false;
        $flat = '';

        for ($i = 0; $i < $length; $i++) {
            $character = $json[$i];
            $hidden = $depth > 1 || ($inString && $depth > 1);

            if ($inString) {
                $flat .= $hidden ? ' ' : $character;

                if ($escape) {
                    $escape = false;

                    continue;
                }

                if ($character === '\\') {
                    $escape = true;

                    continue;
                }

                if ($character === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($character === '"') {
                $inString = true;
                $flat .= $depth > 1 ? ' ' : $character;

                continue;
            }

            if ($character === '{' || $character === '[') {
                $depth++;
                $flat .= $depth > 1 ? ' ' : $character;

                continue;
            }

            if ($character === '}' || $character === ']') {
                $flat .= $depth > 1 ? ' ' : $character;
                $depth--;

                continue;
            }

            $flat .= $depth > 1 ? ' ' : $character;
        }

        return $flat;
    }

    private function topLevelJsonNumber(string $json, string $key): ?string
    {
        $matched = preg_match(
            '/"'.preg_quote($key, '/').'"\s*:\s*"?(?<amount>\d+(?:\.\d+)?)"?/',
            $json,
            $matches,
        ) === 1;

        return $matched ? $matches['amount'] : null;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.flutterwave.base_url'), '/');
    }

    /**
     * @return array<string, array{label: string, options: string}>
     */
    private function currencies(): array
    {
        $currencies = config('services.flutterwave.currencies');

        return is_array($currencies) ? $currencies : [];
    }
}
