<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\UserStatus;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role->value === 'member'
            && in_array($user->status, [UserStatus::Pending, UserStatus::Active], true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount_usd' => trim((string) $this->input('amount_usd')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount_usd' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        $amount = Money::normalize((string) $value, 2);
                        $minimum = Money::normalize((string) setting('min_payment_usd', '10.00'), 2);
                    } catch (InvalidArgumentException) {
                        $fail('Enter a valid USD amount.');

                        return;
                    }

                    if (Money::compare($amount, '999999.99') === 1) {
                        $fail('The maximum payment is 999999.99 USD.');
                    }

                    if (Money::compare($amount, $minimum) < 0) {
                        $fail("The minimum payment is {$minimum} USD.");
                    }
                },
            ],
        ];
    }
}
