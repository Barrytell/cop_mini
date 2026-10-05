@extends('layouts.admin')
@section('title', 'Announcements')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Announcements</h1>
        <a class="btn-primary" href="{{ route('admin.announcements.create') }}">Create</a>
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($announcements as $item)
            <a href="{{ route('admin.announcements.edit', $item) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $item->title }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $item->audience }} · {{ $item->is_published ? 'published' : 'draft' }}{{ $item->is_pinned ? ' · pinned' : '' }}</p>
            </a>
        @empty
            <p class="text-stone-600">No announcements.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $announcements->links() }}</div>
@endsection