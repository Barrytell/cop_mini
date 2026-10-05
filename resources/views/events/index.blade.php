@extends('layouts.public')

@section('title', 'Events and meetings')
@section('meta_description', 'Upcoming and past gatherings for members of the cooperative.')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">Events and meetings</h1>
        <p class="mt-4 text-lg leading-8 text-stone-700">Members meet to hear how new contributions were allocated and to ask questions. Register to receive the attendance link from your account.</p>
        <h2 class="mt-10 font-serif text-2xl text-navy-900">Upcoming</h2>
        <div class="mt-4 space-y-4">
            @forelse ($upcoming as $meeting)
                <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <h3 class="font-serif text-2xl text-navy-900">{{ $meeting->title }}</h3>
                    <p class="mt-2 text-sm font-semibold text-stone-700">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('l, F j, Y g:i A') }}@if ($meeting->ends_at) – {{ $meeting->ends_at->timezone(config('app.timezone'))->format('g:i A') }}@endif</p>
                    @if ($meeting->location)
                        <p class="mt-1 text-sm text-stone-700">{{ $meeting->location }}</p>
                    @endif
                    <p class="mt-3 text-sm leading-6 text-stone-700">{{ $meeting->description }}</p>
                    <p class="mt-2 text-sm text-stone-600">The attendance link is in your account after you join.</p>
                </article>
            @empty
                <p class="text-stone-600">No gatherings are on the calendar.</p>
            @endforelse
        </div>
        <h2 class="mt-10 font-serif text-2xl text-navy-900">Past</h2>
        <div class="mt-4 space-y-3">
            @forelse ($past as $meeting)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <h3 class="font-semibold text-navy-900">{{ $meeting->title }}</h3>
                    <p class="text-sm text-stone-600">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('M j, Y') }} · {{ $meeting->status->value }}</p>
                </article>
            @empty
                <p class="text-stone-600">Past meetings will be listed here.</p>
            @endforelse
        </div>
    </div>
@endsection
