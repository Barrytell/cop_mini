<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContactRequest;
use App\Mail\ContactMessageReceived;
use App\Modules\Cms\Models\ContactMessage;
use App\Modules\Cms\Models\Page;
use App\Support\MapEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact.show', [
            'page' => Page::query()->published()->where('slug', 'contact')->first(),
            'address' => (string) setting('office_address', config('minimini.defaults.office_address')),
            'mapUrl' => MapEmbed::url((string) setting('map_embed_url', config('minimini.defaults.map_embed_url'))),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = ContactMessage::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->input('phone'),
            'subject' => $request->string('subject')->toString(),
            'body' => $request->string('body')->toString(),
            'ip_address' => $request->ip(),
        ]);

        $inbox = (string) setting('contact_email', config('minimini.defaults.contact_email'));

        if (filter_var($inbox, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($inbox)->queue(new ContactMessageReceived($contact));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('contact')->with('status', 'Message received. The cooperative will reply by email.');
    }
}
