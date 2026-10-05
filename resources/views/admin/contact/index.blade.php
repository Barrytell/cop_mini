@extends('layouts.admin')
@section('title', 'Contact inbox')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Contact messages</h1>
        <a class="btn-secondary" href="{{ route('admin.contact.index', ['unread' => 1]) }}">Unread only</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($messages as $message)
            <a href="{{ route('admin.contact.show', $message) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200 {{ $message->is_read ? '' : 'ring-gold-500' }}">
                <p class="font-semibold">{{ $message->subject }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $message->name }} · {{ $message->email }} · {{ $message->is_read ? 'read' : 'unread' }}</p>
            </a>
        @empty
            <p class="text-stone-600">Inbox empty.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
@endsection