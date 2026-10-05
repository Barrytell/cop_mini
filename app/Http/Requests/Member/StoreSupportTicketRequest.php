<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array($user->status, [UserStatus::Pending, UserStatus::Active], true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subject' => trim((string) $this->input('subject')),
            'body' => trim((string) $this->input('body')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
