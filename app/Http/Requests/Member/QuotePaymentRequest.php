<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotePaymentRequest extends FormRequest
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
            'amount_usd' => trim((string) $this->query('amount_usd', $this->input('amount_usd'))),
            'currency' => strtoupper(trim((string) $this->query('currency', $this->input('currency', 'USD')))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currencies = config('services.flutterwave.currencies');

        return [
            'amount_usd' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'currency' => ['required', 'string', Rule::in(array_keys(is_array($currencies) ? $currencies : []))],
        ];
    }
}
