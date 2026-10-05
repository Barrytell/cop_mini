<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('member.notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $this->owned($request, $notification);
        $record?->markAsRead();

        return $this->follow($record);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('member.notifications.index')->with('status', 'Notifications marked as read.');
    }

    private function owned(Request $request, string $id): ?DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->first();
    }

    private function follow(?DatabaseNotification $notification): RedirectResponse
    {
        $ticketId = $notification?->data['ticket_id'] ?? null;

        if (is_numeric($ticketId)) {
            return redirect()->route('member.support.show', ['ticket' => $ticketId]);
        }

        if (isset($notification?->data['tx_ref'])) {
            return redirect()->route('member.payments.index');
        }

        if (isset($notification?->data['bonus_units'])) {
            return redirect()->route('member.referrals.index');
        }

        return redirect()->route('member.notifications.index');
    }
}
