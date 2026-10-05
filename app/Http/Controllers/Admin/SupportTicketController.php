<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplySupportTicketRequest;
use App\Modules\Support\Models\SupportTicket;
use App\Notifications\SupportTicketReplied;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        return view('admin.support.index', [
            'tickets' => SupportTicket::query()->with('member')->latest()->paginate(20),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['member', 'messages.author']);

        return view('admin.support.show', ['ticket' => $ticket]);
    }

    public function reply(ReplySupportTicketRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        $ticket->member?->notify(new SupportTicketReplied($ticket));

        return redirect()->route('admin.support.show', $ticket)->with('status', 'Reply sent to the member.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->status === TicketStatus::Open, 404);

        $ticket->forceFill([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ])->save();

        return redirect()->route('admin.support.show', $ticket)->with('status', 'Ticket closed.');
    }
}
