<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $code = $this->input('referral_code');

        if (! $this->exists('referral_code')) {
            $code = $this->session()->get('referral_code', $this->cookie('referral_code'));
        }

        $normalized = strtoupper(trim((string) $code));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'referral_code' => $normalized === '' ? null : $normalized,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s().-]{6,18}$/'],
            'country' => ['required', 'string', Rule::in(config('countries'))],
            'password' => ['required', 'confirmed', Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:16', Rule::exists('users', 'referral_code')],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.prohibited' => 'Unable to process this registration.',
            'phone.regex' => 'Enter a valid phone number, including the country code.',
        ];
    }

    public function referralCode(): ?string
    {
        $code = $this->validated('referral_code');

        return is_string($code) && $code !== '' ? $code : null;
    }
}
