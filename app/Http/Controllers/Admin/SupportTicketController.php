<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplySupportTicketRequest;
use App\Modules\Support\Models\SupportTicket;
use App\Notifications\SupportTicketReplied;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::SUPPORT);

        return view('admin.support.index', [
            'tickets' => SupportTicket::query()->with('member')->latest()->paginate(20),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $this->requirePermission(AdminPermission::SUPPORT);
        $this->authorize('view', $ticket);
        $ticket->load(['member', 'messages.author']);

        return view('admin.support.show', ['ticket' => $ticket]);
    }

    public function reply(ReplySupportTicketRequest $request, SupportTicket $ticket, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SUPPORT);
        $this->authorize('reply', $ticket);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        $ticket->member?->notify(new SupportTicketReplied($ticket));
        $audit->record($request->user(), 'admin.support.replied', $ticket, null, [
            'ticket_id' => $ticket->id,
        ], $request);

        return redirect()->route('admin.support.show', $ticket)->with('status', 'Reply sent to the member.');
    }

    public function close(Request $request, SupportTicket $ticket, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SUPPORT);
        $this->authorize('close', $ticket);
        abort_unless($ticket->status === TicketStatus::Open, 404);

        $ticket->forceFill([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ])->save();

        $audit->record($request->user(), 'admin.support.closed', $ticket, null, [
            'ticket_id' => $ticket->id,
        ], $request);

        return redirect()->route('admin.support.show', $ticket)->with('status', 'Ticket closed.');
    }
}
