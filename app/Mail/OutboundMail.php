<?php

declare(strict_types=1);

namespace App\Mail;

use App\Modules\Communications\Models\OutboundMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OutboundMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public OutboundMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->message->subject ?: 'Message from minimini.org');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.outbound');
    }
}
