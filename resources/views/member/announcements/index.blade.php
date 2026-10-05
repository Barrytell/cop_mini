@extends('layouts.member')

@section('title', 'Announcements')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Announcements</h1>
    <div class="mt-6 space-y-3">
        @forelse ($announcements as $announcement)
            <a href="{{ route('member.announcements.show', $announcement) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $announcement->title }}</p>
                    @unless ($announcement->readBy(auth()->user()))
                        <span class="inline-flex min-h-7 items-center rounded-full bg-gold-500 px-2 text-xs font-semibold">Unread</span>
                    @endunless
                </div>
                <p class="mt-1 text-sm text-stone-600">{{ $announcement->published_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
            </a>
        @empty
            <p class="text-stone-600">No announcements yet.</p>
        @endforelse
    </div>
    <div class="mt-6 overflow-x-auto">{{ $announcements->links() }}</div>
@endsection
