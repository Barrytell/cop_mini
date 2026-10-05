<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Announcements\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $announcements = Announcement::query()
            ->published()
            ->with(['reads' => fn ($query) => $query->where('user_id', $user->id)])
            ->latest('published_at')
            ->paginate(12);

        return view('member.announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        abort_unless($announcement->is_published, 404);

        AnnouncementRead::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'announcement_id' => $announcement->id,
            ],
            ['read_at' => now()],
        );

        return view('member.announcements.show', [
            'announcement' => $announcement,
        ]);
    }
}
