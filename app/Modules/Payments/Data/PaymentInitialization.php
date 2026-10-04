<?php

declare(strict_types=1);

namespace App\Modules\Payments\Data;

final readonly class PaymentInitialization
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $redirectUrl,
        public array $raw,
    ) {}
}
