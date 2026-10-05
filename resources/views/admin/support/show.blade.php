@extends('layouts.admin')

@section('title', $ticket->subject)

@section('content')
    <a href="{{ route('admin.support.index') }}" class="inline-flex min-h-11 items-center font-semibold">All messages</a>
    <h1 class="mt-3 font-serif text-3xl">{{ $ticket->subject }}</h1>
    <p class="mt-2 text-sm text-stone-600">{{ $ticket->member?->name }} · {{ $ticket->member?->email }} · {{ $ticket->status->value }}</p>
    <div class="mt-6 space-y-3">
        @foreach ($ticket->messages as $message)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="text-sm font-semibold">{{ $message->author?->isAdmin() ? 'Staff' : ($message->author?->name ?? 'Member') }}</p>
                <p class="mt-2 whitespace-pre-wrap">{{ $message->body }}</p>
            </article>
        @endforeach
    </div>
    @if ($ticket->status->value === 'open')
        <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="mt-6 space-y-3">
            @csrf
            <label class="label" for="body">Reply</label>
            <textarea class="field min-h-32" id="body" name="body" required>{{ old('body') }}</textarea>
            <button type="submit" class="btn-primary">Send reply</button>
        </form>
        <form method="POST" action="{{ route('admin.support.close', $ticket) }}" class="mt-3">
            @csrf
            <button type="submit" class="btn-ghost">Close ticket</button>
        </form>
    @endif
@endsection
