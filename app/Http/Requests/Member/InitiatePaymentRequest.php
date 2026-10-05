<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\UserStatus;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
        $amount = $this->input('amount_usd');
        $currency = $this->input('currency', 'USD');
        $currency = is_string($currency) ? strtoupper(trim($currency)) : '';

        $this->merge([
            'amount_usd' => is_string($amount) ? trim($amount) : '',
            'currency' => $currency === '' ? 'USD' : $currency,
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
            'currency' => ['required', 'string', Rule::in(array_keys($this->currencies()))],
        ];
    }

    /**
     * @return array<string, array{label?: string, options?: string}>
     */
    private function currencies(): array
    {
        $currencies = config('services.flutterwave.currencies');

        return is_array($currencies) ? $currencies : [];
    }
}
