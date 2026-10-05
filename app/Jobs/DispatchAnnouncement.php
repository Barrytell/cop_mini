<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\AnnouncementMail;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Notifications\AnnouncementPublished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DispatchAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $announcementId) {}

    public function handle(): void
    {
        $announcement = Announcement::query()->find($this->announcementId);

        if ($announcement === null || ! $announcement->is_published) {
            return;
        }

        $query = User::query()->where('role', UserRole::Member);

        $query = match ($announcement->audience) {
            'active' => $query->where('status', UserStatus::Active),
            'pending' => $query->where('status', UserStatus::Pending),
            'selected' => $query->whereIn('id', $announcement->audience_user_ids ?? []),
            default => $query,
        };

        $query->orderBy('id')->chunkById(100, function ($users) use ($announcement): void {
            foreach ($users as $user) {
                if ($announcement->send_notification && $user->notify_announcements) {
                    $user->notify(new AnnouncementPublished($announcement));
                }

                if ($announcement->send_email && $user->notify_announcements) {
                    Mail::to($user->email)->queue(new AnnouncementMail($announcement));
                }
            }
        });
    }
}
