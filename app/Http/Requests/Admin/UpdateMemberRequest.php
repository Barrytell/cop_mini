<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('member')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->id)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s().-]{6,18}$/'],
            'country' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
