<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Modules\Referrals\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralBonusReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Referral $referral) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Referral bonus units credited')
            ->greeting('Hello '.$notifiable->name)
            ->line('A member you referred is now active.')
            ->line(number_format((int) $this->referral->bonus_units).' bonus units were added to your account.')
            ->action('Open your dashboard', route('member.dashboard'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'referral_id' => $this->referral->id,
            'referred_id' => $this->referral->referred_id,
            'bonus_units' => (int) $this->referral->bonus_units,
        ];
    }
}
