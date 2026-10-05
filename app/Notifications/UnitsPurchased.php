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
        return ($notifiable->notify_payments ?? true) ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Receipt '.$this->payment->tx_ref)
            ->greeting('Hello '.$notifiable->name)
            ->line('Your payment was confirmed.')
            ->line('Reference: '.$this->payment->tx_ref)
            ->line('USD amount: $'.$this->payment->amount_usd)
            ->line('Charged: '.$this->payment->amount_paid.' '.$this->payment->currency_paid)
            ->line(number_format((int) $this->payment->units_purchased).' units at $'.$this->payment->unit_price_snapshot.' each.')
            ->line('Member number: '.$notifiable->member_no)
            ->action('View payment history', route('member.payments.index'));
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
            'amount_paid' => (string) $this->payment->amount_paid,
            'currency_paid' => (string) $this->payment->currency_paid,
            'member_no' => $notifiable->member_no,
        ];
    }
}
