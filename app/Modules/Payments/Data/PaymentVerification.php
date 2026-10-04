<?php

declare(strict_types=1);

namespace App\Modules\Payments\Data;

final readonly class PaymentVerification
{
    public function __construct(
        public bool $successful,
        public string $transactionId,
        public string $txRef,
        public string $amount,
        public string $currency,
        public string $rawBody,
        public string $gatewayStatus = '',
    ) {}
}
