<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Models\MeetingRsvp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $withRsvp = fn ($query) => $query->with(['rsvps' => fn ($rsvps) => $rsvps->where('user_id', $user->id)]);

        return view('member.meetings.index', [
            'upcoming' => Meeting::query()
                ->where('status', MeetingStatus::Scheduled)
                ->where('starts_at', '>=', now())
                ->tap($withRsvp)
                ->orderBy('starts_at')
                ->get(),
            'past' => Meeting::query()
                ->where(function ($query): void {
                    $query->where('starts_at', '<', now())
                        ->orWhere('status', '!=', MeetingStatus::Scheduled->value);
                })
                ->tap($withRsvp)
                ->orderByDesc('starts_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function rsvp(Request $request, Meeting $meeting): RedirectResponse
    {
        abort_unless(
            $meeting->status === MeetingStatus::Scheduled && $meeting->starts_at->isFuture(),
            403,
            'This meeting is no longer open for attendance.',
        );

        $existing = MeetingRsvp::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing?->attending) {
            $existing->forceFill(['attending' => false])->save();
            $message = 'Your attendance was cancelled.';
        } else {
            MeetingRsvp::query()->updateOrCreate(
                ['meeting_id' => $meeting->id, 'user_id' => $request->user()->id],
                ['attending' => true],
            );
            $message = 'You are marked as attending.';
        }

        return redirect()->route('member.meetings.index')->with('status', $message);
    }
}
