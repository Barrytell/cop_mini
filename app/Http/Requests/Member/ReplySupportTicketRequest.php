<?php

declare(strict_types=1);

namespace App\Http\Requests\Member;

use App\Enums\TicketStatus;
use App\Enums\UserStatus;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;

class ReplySupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $ticket = $this->route('ticket');

        return $user !== null
            && $ticket instanceof SupportTicket
            && $user->id === $ticket->user_id
            && $ticket->status === TicketStatus::Open
            && in_array($user->status, [UserStatus::Pending, UserStatus::Active], true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => trim((string) $this->input('body')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
