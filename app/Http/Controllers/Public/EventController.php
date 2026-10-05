<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events.index', [
            'upcoming' => Meeting::query()
                ->where('status', MeetingStatus::Scheduled)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->get(),
            'past' => Meeting::query()
                ->where(function ($query): void {
                    $query->where('starts_at', '<', now())
                        ->orWhere('status', '!=', MeetingStatus::Scheduled->value);
                })
                ->orderByDesc('starts_at')
                ->limit(12)
                ->get(),
        ]);
    }
}
