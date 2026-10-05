<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TicketStatus;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;

class ReplySupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $this->user()?->isAdmin() === true
            && $ticket instanceof SupportTicket
            && $ticket->status === TicketStatus::Open;
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
