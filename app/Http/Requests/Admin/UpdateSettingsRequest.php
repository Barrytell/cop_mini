<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Modules\Settings\Models\Setting;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Setting::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $social = [];

        foreach (['social_facebook', 'social_x', 'social_instagram', 'social_linkedin', 'social_youtube'] as $field) {
            $value = trim((string) $this->input($field));
            $social[$field] = $value === '' ? null : $value;
        }

        $this->merge([
            'unit_price_usd' => trim((string) $this->input('unit_price_usd')),
            'min_payment_usd' => trim((string) $this->input('min_payment_usd')),
            'site_name' => trim((string) $this->input('site_name')),
            'contact_email' => strtolower(trim((string) $this->input('contact_email'))),
            'whatsapp_number' => ($phone = trim((string) $this->input('whatsapp_number'))) === '' ? null : $phone,
            'office_address' => ($address = trim((string) $this->input('office_address'))) === '' ? null : $address,
            'map_embed_url' => ($map = trim((string) $this->input('map_embed_url'))) === '' ? null : $map,
            ...$social,
        ]);
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
            'referral_bonus_units' => ['required', 'integer', 'min:0', 'max:1000'],
            'min_payment_usd' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    $amount = Money::normalize((string) $value, 2);
                } catch (InvalidArgumentException) {
                    $fail('Enter a valid minimum payment.');

                    return;
                }

                if (Money::compare($amount, '0.01') < 0 || Money::compare($amount, '999999.99') === 1) {
                    $fail('Minimum payment must be between 0.01 and 999999.99 USD.');
                }
            }],
            'site_name' => ['required', 'string', 'max:80'],
            'contact_email' => ['required', 'string', 'email', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'social_x' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'social_instagram' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'social_linkedin' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'social_youtube' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^\+?[0-9][0-9\s().-]{7,18}$/'],
            'office_address' => ['nullable', 'string', 'max:500'],
            'map_embed_url' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || \App\Support\MapEmbed::url($value) === null) {
                    $fail('Use an https link from OpenStreetMap or Google Maps.');
                }
            }],
        ];
    }
}
