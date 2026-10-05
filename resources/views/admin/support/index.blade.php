@extends('layouts.admin')

@section('title', 'Support')

@section('content')
    <h1 class="font-serif text-3xl font-semibold">Support</h1>
    <div class="mt-6 space-y-3">
        @forelse ($tickets as $ticket)
            <a href="{{ route('admin.support.show', $ticket) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $ticket->subject }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $ticket->member?->name }} · {{ $ticket->status->value }}</p>
            </a>
        @empty
            <p class="text-stone-600">No messages.</p>
        @endforelse
    </div>
    <div class="mt-6 overflow-x-auto">{{ $tickets->links() }}</div>
@endsection
