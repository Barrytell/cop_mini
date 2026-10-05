<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\AdminPermission;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class UpdateUnitSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && AdminPermission::allows($this->user(), AdminPermission::SETTINGS);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_price_usd' => ['required', 'regex:/^\d+(\.\d{1,6})?$/', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    $price = Money::normalize((string) $value, 6);
                } catch (InvalidArgumentException) {
                    $fail('Enter a valid unit price.');

                    return;
                }

                if (Money::compare($price, '0.000001', 6) < 0 || Money::compare($price, '1000', 6) === 1) {
                    $fail('Unit price must be between 0.000001 and 1000 USD.');
                }
            }],
            'min_payment_usd' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'referral_bonus_units' => ['required', 'integer', 'min:0', 'max:1000'],
            'registration_open' => ['sometimes', 'boolean'],
            'referral_program_enabled' => ['sometimes', 'boolean'],
            'maintenance_mode' => ['sometimes', 'boolean'],
        ];
    }
}
