<?php

declare(strict_types=1);

namespace App\Mail;

use App\Modules\Cms\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Website message: '.$this->contact->subject,
            replyTo: [$this->contact->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-message');
    }
}
