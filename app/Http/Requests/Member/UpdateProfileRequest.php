<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $blank = fn (string $key): ?string => ($value = trim((string) $this->input($key))) === '' ? null : $value;

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => trim((string) $this->input('phone')),
            'kin_name' => $blank('kin_name'),
            'kin_relationship' => $blank('kin_relationship'),
            'kin_phone' => $blank('kin_phone'),
            'payout_bank_name' => $blank('payout_bank_name'),
            'payout_account_name' => $blank('payout_account_name'),
            'payout_account_number' => $blank('payout_account_number'),
            'payout_routing_code' => $blank('payout_routing_code'),
            'notify_payments' => $this->boolean('notify_payments'),
            'notify_announcements' => $this->boolean('notify_announcements'),
            'notify_meetings' => $this->boolean('notify_meetings'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s().-]{6,18}$/'],
            'country' => ['required', 'string', Rule::in(config('countries'))],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'kin_name' => ['nullable', 'string', 'max:120'],
            'kin_relationship' => ['nullable', 'string', 'max:80'],
            'kin_phone' => ['nullable', 'string', 'regex:/^\+?[0-9][0-9\s().-]{6,18}$/'],
            'payout_bank_name' => ['nullable', 'string', 'max:120'],
            'payout_account_name' => ['nullable', 'string', 'max:120'],
            'payout_account_number' => ['nullable', 'string', 'regex:/^[0-9\s-]{4,40}$/'],
            'payout_routing_code' => ['nullable', 'string', 'max:40'],
            'notify_payments' => ['boolean'],
            'notify_announcements' => ['boolean'],
            'notify_meetings' => ['boolean'],
        ];
    }
}
