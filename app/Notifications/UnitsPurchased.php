<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Modules\Payments\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnitsPurchased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

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
            ->subject('Your minimini.org units are in')
            ->greeting('Hello '.$notifiable->name)
            ->line('Payment '.$this->payment->tx_ref.' was confirmed.')
            ->line(number_format((int) $this->payment->units_purchased).' units were added to your account.')
            ->action('Open your dashboard', route('member.dashboard'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'tx_ref' => $this->payment->tx_ref,
            'units' => (int) $this->payment->units_purchased,
            'amount_usd' => (string) $this->payment->amount_usd,
        ];
    }
}
