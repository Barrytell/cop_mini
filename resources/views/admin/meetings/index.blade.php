@extends('layouts.admin')
@section('title', 'Meetings')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Meetings</h1>
        <a class="btn-primary" href="{{ route('admin.meetings.create') }}">Create</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($meetings as $meeting)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $meeting->title }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $meeting->starts_at }} · {{ $meeting->status->value }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a class="btn-secondary" href="{{ route('admin.meetings.edit', $meeting) }}">Edit</a>
                        <a class="btn-secondary" href="{{ route('admin.meetings.rsvps', $meeting) }}">RSVPs</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-stone-600">No meetings.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $meetings->links() }}</div>
@endsection