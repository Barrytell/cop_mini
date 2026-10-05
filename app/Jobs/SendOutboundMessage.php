<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\OutboundMail;
use App\Models\User;
use App\Modules\Communications\Models\OutboundMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOutboundMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $messageId) {}

    public function handle(): void
    {
        $message = OutboundMessage::query()->find($this->messageId);

        if ($message === null) {
            return;
        }

        $query = User::query()->where('role', UserRole::Member);
        $query = match ($message->audience) {
            'active' => $query->where('status', UserStatus::Active),
            'pending' => $query->where('status', UserStatus::Pending),
            default => $query,
        };

        $query->orderBy('id')->chunkById(100, function ($users) use ($message): void {
            foreach ($users as $user) {
                if ($message->channel === 'email') {
                    Mail::to($user->email)->queue(new OutboundMail($message));
                } else {
                    Log::channel('single')->info('sms.ready', [
                        'to' => $user->phone,
                        'body' => $message->body,
                        'outbound_id' => $message->id,
                    ]);
                }
            }
        });

        $message->forceFill(['status' => 'sent'])->save();
    }
}
