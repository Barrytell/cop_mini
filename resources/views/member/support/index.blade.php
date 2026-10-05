@extends('layouts.member')

@section('title', 'Support')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Support</h1>
        <a href="{{ route('member.support.create') }}" class="btn-primary">New message</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($tickets as $ticket)
            <a href="{{ route('member.support.show', $ticket) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $ticket->subject }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $ticket->status->value }} · {{ $ticket->created_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
            </a>
        @empty
            <p class="text-stone-600">You have not sent a message yet.</p>
        @endforelse
    </div>
    <div class="mt-6 overflow-x-auto">{{ $tickets->links() }}</div>
@endsection
