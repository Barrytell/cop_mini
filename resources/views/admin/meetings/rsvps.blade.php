@extends('layouts.admin')
@section('title', 'RSVPs')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">RSVPs · {{ $meeting->title }}</h1>
        <a class="btn-secondary" href="{{ route('admin.meetings.rsvps', [$meeting, 'export' => 'csv']) }}">Export attendance</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($meeting->rsvps as $rsvp)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $rsvp->user?->name }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $rsvp->user?->email }} · {{ $rsvp->attending ? 'attending' : 'not attending' }}</p>
            </article>
        @empty
            <p class="text-stone-600">No RSVPs yet.</p>
        @endforelse
    </div>
@endsection