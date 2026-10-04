<?php

declare(strict_types=1);

namespace App\Modules\Payments\Contracts;

use App\Models\User;
use App\Modules\Payments\Data\PaymentInitialization;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Models\Payment;

interface PaymentGatewayInterface
{
    public function initialize(Payment $payment, User $user, string $redirectUrl): PaymentInitialization;

    public function verify(string $transactionId): PaymentVerification;
}
