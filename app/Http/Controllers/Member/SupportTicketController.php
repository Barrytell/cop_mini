<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\ReplySupportTicketRequest;
use App\Http\Requests\Member\StoreSupportTicketRequest;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        return view('member.support.index', [
            'tickets' => $request->user()->supportTickets()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('member.support.create');
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $ticket = DB::transaction(function () use ($request): SupportTicket {
            $ticket = $request->user()->supportTickets()->create([
                'subject' => $request->string('subject')->toString(),
                'status' => TicketStatus::Open,
            ]);

            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'body' => $request->string('body')->toString(),
            ]);

            return $ticket;
        });

        return redirect()->route('member.support.show', $ticket)->with('status', 'Message sent to the cooperative.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);
        $ticket->load(['messages.author']);

        return view('member.support.show', ['ticket' => $ticket]);
    }

    public function reply(ReplySupportTicketRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        return redirect()->route('member.support.show', $ticket)->with('status', 'Reply sent.');
    }
}
