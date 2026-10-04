<?php

declare(strict_types=1);

namespace App\Modules\Payments\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\User;
use App\Modules\Payments\Exceptions\ImmutablePaymentException;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tx_ref',
        'flw_transaction_id',
        'amount_usd',
        'currency_paid',
        'amount_paid',
        'unit_price_snapshot',
        'units_purchased',
        'type',
        'status',
        'gateway_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'unit_price_snapshot' => 'decimal:6',
            'units_purchased' => 'integer',
            'type' => PaymentType::class,
            'status' => PaymentStatus::class,
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Payment $payment): void {
            if ($payment->getOriginal('status') === PaymentStatus::Successful->value) {
                throw new ImmutablePaymentException('Successful payments are immutable.');
            }

            foreach (['user_id', 'tx_ref', 'amount_usd', 'unit_price_snapshot', 'units_purchased', 'type'] as $field) {
                if (! $payment->isDirty($field)) {
                    continue;
                }

                $original = $payment->getOriginal($field);
                $current = $payment->{$field};

                if ($original instanceof \BackedEnum) {
                    $original = $original->value;
                }

                if ($current instanceof \BackedEnum) {
                    $current = $current->value;
                }

                if ((string) $original === (string) $current) {
                    continue;
                }

                throw new ImmutablePaymentException("Payment {$field} cannot be changed.");
            }
        });

        static::deleting(function (): void {
            throw new ImmutablePaymentException('Payments cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }
}
