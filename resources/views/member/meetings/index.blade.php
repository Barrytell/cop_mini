@extends('layouts.member')

@section('title', 'Meetings')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Meetings</h1>

    <section class="mt-6">
        <h2 class="font-serif text-2xl">Upcoming</h2>
        <div class="mt-3 space-y-3">
            @forelse ($upcoming as $meeting)
                @php($rsvp = $meeting->rsvpFor(auth()->user()))
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <h3 class="font-semibold">{{ $meeting->title }}</h3>
                    <p class="mt-1 text-sm text-stone-700">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A') }}@if ($meeting->ends_at) – {{ $meeting->ends_at->timezone(config('app.timezone'))->format('g:i A') }}@endif</p>
                    @if ($meeting->location)<p class="mt-1 text-sm">{{ $meeting->location }}</p>@endif
                    @if ($meeting->description)<p class="mt-2 whitespace-pre-wrap text-sm text-stone-700">{{ $meeting->description }}</p>@endif
                    @if ($join = safe_url($meeting->meeting_url))
                        <a href="{{ $join }}" class="mt-2 inline-flex min-h-11 items-center break-all font-semibold text-forest-800" rel="noopener noreferrer">Online link</a>
                    @endif
                    <form method="POST" action="{{ route('member.meetings.rsvp', $meeting) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-primary">{{ $rsvp?->attending ? 'Cancel attendance' : 'I will attend' }}</button>
                    </form>
                </article>
            @empty
                <p class="text-stone-600">No upcoming meetings.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Past</h2>
        <div class="mt-3 space-y-3">
            @forelse ($past as $meeting)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <h3 class="font-semibold">{{ $meeting->title }}</h3>
                    <p class="mt-1 text-sm text-stone-600">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A') }}</p>
                    @if ($meeting->location)<p class="mt-1 text-sm">{{ $meeting->location }}</p>@endif
                </article>
            @empty
                <p class="text-stone-600">No past meetings.</p>
            @endforelse
        </div>
    </section>
@endsection
