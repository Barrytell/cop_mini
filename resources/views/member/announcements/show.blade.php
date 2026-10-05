@extends('layouts.member')

@section('title', $announcement->title)

@section('content')
    <a href="{{ route('member.announcements.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">All announcements</a>
    <h1 class="mt-3 font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">{{ $announcement->title }}</h1>
    <p class="mt-2 text-sm text-stone-600">{{ $announcement->published_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
    <div class="mt-6 whitespace-pre-wrap rounded-3xl bg-white p-5 text-stone-800 ring-1 ring-stone-200">{{ $announcement->body }}</div>
@endsection
