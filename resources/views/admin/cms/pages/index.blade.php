@extends('layouts.admin')
@section('title', 'Pages')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">CMS pages</h1>
    <div class="mt-6 space-y-3">
        @forelse ($pages as $page)
            <a href="{{ route('admin.cms.pages.edit', $page) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $page->title }}</p>
                <p class="mt-1 text-sm text-stone-600">/{{ $page->slug }} · {{ $page->is_published ? 'published' : 'draft' }}</p>
            </a>
        @empty
            <p class="text-stone-600">No pages.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $pages->links() }}</div>
@endsection