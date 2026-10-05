@extends('layouts.member')

@section('title', $ticket->subject)

@section('content')
    <a href="{{ route('member.support.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">All messages</a>
    <h1 class="mt-3 font-serif text-3xl font-semibold text-forest-900">{{ $ticket->subject }}</h1>
    <p class="mt-2 text-sm text-stone-600">{{ $ticket->status->value }}</p>
    <div class="mt-6 space-y-3">
        @foreach ($ticket->messages as $message)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="text-sm font-semibold">{{ $message->author?->isAdmin() ? 'Cooperative' : ($message->author?->name ?? 'Member') }}</p>
                <p class="mt-2 whitespace-pre-wrap">{{ $message->body }}</p>
                <p class="mt-2 text-xs text-stone-500">{{ $message->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
            </article>
        @endforeach
    </div>
    @if ($ticket->status->value === 'open')
        <form method="POST" action="{{ route('member.support.reply', $ticket) }}" class="mt-6 space-y-3">
            @csrf
            <label class="label" for="body">Reply</label>
            <textarea class="field min-h-32" id="body" name="body" required maxlength="5000">{{ old('body') }}</textarea>
            <button type="submit" class="btn-primary">Send reply</button>
        </form>
    @endif
@endsection
