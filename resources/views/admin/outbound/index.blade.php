@extends('layouts.admin')
@section('title', 'Outbound')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Bulk email / SMS</h1>
        <a class="btn-primary" href="{{ route('admin.outbound.create') }}">Compose</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($messages as $message)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $message->channel }} · {{ $message->subject ?: 'SMS' }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $message->audience }} · {{ $message->recipient_count }} recipients · {{ $message->status }}</p>
            </article>
        @empty
            <p class="text-stone-600">No outbound messages.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
@endsection