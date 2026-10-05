@extends('layouts.admin')
@section('title', 'Banners')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Banners</h1>
        <a class="btn-primary" href="{{ route('admin.banners.create') }}">Upload</a>
    </div>
    <form method="POST" action="{{ route('admin.banners.reorder') }}" class="mt-6 space-y-3">
        @csrf
        @foreach ($banners as $banner)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex flex-wrap items-center gap-4">
                    <input type="hidden" name="order[]" value="{{ $banner->id }}">
                    <img src="{{ asset($banner->image_path) }}" alt="" class="h-16 w-28 rounded-xl object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">{{ $banner->title ?: 'Untitled' }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $banner->position }} · order {{ $banner->sort_order }} · {{ $banner->is_active ? 'enabled' : 'disabled' }}</p>
                    </div>
                    <a class="btn-secondary" href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                </div>
            </article>
        @endforeach
        @if($banners->isNotEmpty())
            <button class="btn-primary" type="submit">Save order (current list order)</button>
        @endif
    </form>
@endsection