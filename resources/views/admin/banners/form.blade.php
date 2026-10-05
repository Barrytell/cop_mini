@extends('layouts.admin')
@section('title', $banner->exists ? 'Edit banner' : 'Create banner')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $banner->exists ? 'Edit' : 'Create' }} banner</h1>
    @if($banner->image_path)
        <div class="mt-4 overflow-hidden rounded-3xl bg-stone-200">
            <img src="{{ asset($banner->image_path) }}" alt="" class="max-h-64 w-full object-cover">
            <div class="bg-forest-900/80 p-4 text-cream">
                <p class="font-serif text-2xl">{{ $banner->title }}</p>
                <p class="mt-1 text-sm">{{ $banner->subtitle }}</p>
            </div>
        </div>
    @endif
    <form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @if($banner->exists) @method('PUT') @endif
        <div>
            <label class="label" for="position">Assign to</label>
            <select class="field" id="position" name="position" required>
                <option value="home_hero" @selected(old('position', $banner->position) === 'home_hero')>Home</option>
                <option value="member_dashboard" @selected(old('position', $banner->position) === 'member_dashboard')>Member dashboard</option>
            </select>
        </div>
        <div><label class="label" for="title">Title</label><input class="field" id="title" name="title" value="{{ old('title', $banner->title) }}"></div>
        <div><label class="label" for="subtitle">Subtitle</label><input class="field" id="subtitle" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}"></div>
        <div><label class="label" for="link_url">Link</label><input class="field" id="link_url" name="link_url" type="url" value="{{ old('link_url', $banner->link_url) }}"></div>
        <div><label class="label" for="sort_order">Sort order</label><input class="field" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $banner->sort_order ?? 1) }}" required></div>
        <div><label class="label" for="image">Image</label><input class="field" id="image" name="image" type="file" accept="image/*"></div>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))> Enabled</label>
        <div class="flex flex-wrap gap-2">
            <button class="btn-primary" type="submit">Save</button>
            @if($banner->exists)
                <button class="btn-secondary" form="delete-banner" type="submit">Delete</button>
            @endif
        </div>
    </form>
    @if($banner->exists)
        <form id="delete-banner" method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete banner?')">@csrf @method('DELETE')</form>
    @endif
@endsection