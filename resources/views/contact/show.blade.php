@extends('layouts.public')

@section('title', $page?->meta_title ?: 'Contact')
@section('meta_description', $page?->meta_description ?: 'Write to the cooperative. Messages are saved and emailed to the office.')

@section('content')
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 lg:grid-cols-2">
        <div>
            <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">Contact</h1>
            <p class="mt-4 text-lg leading-8 text-stone-700">{{ $page?->excerpt ?: 'Questions about membership, payments, and meetings come to the same desk.' }}</p>
            @if ($address !== '')
                <p class="mt-6 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $address }}</p>
            @endif
            <a href="mailto:{{ $contactEmail }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-navy-800">{{ $contactEmail }}</a>
            @if ($mapUrl)
                <iframe class="mt-6 h-64 w-full rounded-3xl border-0" src="{{ $mapUrl }}" title="Office map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            @endif
        </div>
        <form method="POST" action="{{ route('contact.store') }}" class="space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            @csrf
            @include('layouts.partials.flash')
            <div aria-hidden="true" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">
                <label for="website">Website</label>
                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <div>
                <label class="label" for="name">Name</label>
                <input class="field" id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="phone">Phone <span class="font-normal text-stone-500">(optional)</span></label>
                <input class="field" id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="subject">Subject</label>
                <input class="field" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160">
                @error('subject')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="body">Message</label>
                <textarea class="field min-h-36" id="body" name="body" required maxlength="5000">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary">Send message</button>
        </form>
    </div>
@endsection
