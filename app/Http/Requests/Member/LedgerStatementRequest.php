<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\LedgerType;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LedgerStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array($user->status, [UserStatus::Pending, UserStatus::Active], true);
    }

    protected function prepareForValidation(): void
    {
        $type = $this->query('type', $this->input('type'));
        $from = $this->query('from', $this->input('from'));
        $to = $this->query('to', $this->input('to'));

        $this->merge([
            'type' => is_string($type) ? $type : '',
            'from' => is_string($from) ? $from : '',
            'to' => is_string($to) ? $to : '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', Rule::in(array_column(LedgerType::cases(), 'value'))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
