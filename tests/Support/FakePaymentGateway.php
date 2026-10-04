<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\User;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Data\PaymentInitialization;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Exceptions\PaymentNotFoundException;
use App\Modules\Payments\Models\Payment;
use App\Support\Money;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public string $redirectUrl = 'https://checkout.flutterwave.com/pay/fake';

    /** @var array<string, PaymentVerification> */
    public array $byId = [];

    /** @var array<string, PaymentVerification> */
    public array $byReference = [];

    public ?PaymentGatewayException $initializeException = null;

    public function initialize(Payment $payment, User $user, string $redirectUrl): PaymentInitialization
    {
        if ($this->initializeException !== null) {
            throw $this->initializeException;
        }

        return new PaymentInitialization($this->redirectUrl, [
            'link' => $this->redirectUrl,
        ]);
    }

    public function verify(string $transactionId): PaymentVerification
    {
        if (! isset($this->byId[$transactionId])) {
            throw new PaymentNotFoundException('No Flutterwave transaction for this reference yet.');
        }

        return $this->byId[$transactionId];
    }

    public function verifyByReference(string $txRef): PaymentVerification
    {
        if (! isset($this->byReference[$txRef])) {
            throw new PaymentNotFoundException('No Flutterwave transaction for this reference yet.');
        }

        return $this->byReference[$txRef];
    }

    public function quote(string $amountUsd, string $currency): string
    {
        return Money::normalize($amountUsd, 2);
    }
}
