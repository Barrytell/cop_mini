@extends('layouts.admin')
@section('title', 'Edit page')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Edit {{ $page->title }}</h1>
    <form method="POST" action="{{ route('admin.cms.pages.update', $page) }}" class="mt-6 max-w-3xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <div><label class="label" for="title">Title</label><input class="field" id="title" name="title" value="{{ old('title', $page->title) }}" required></div>
        <div><label class="label" for="slug">Slug</label><input class="field" id="slug" name="slug" value="{{ old('slug', $page->slug) }}" required></div>
        <div><label class="label" for="template">Template</label><input class="field" id="template" name="template" value="{{ old('template', $page->template) }}" required></div>
        <div><label class="label" for="excerpt">Excerpt</label><textarea class="field" id="excerpt" name="excerpt">{{ old('excerpt', $page->excerpt) }}</textarea></div>
        <div><label class="label" for="body">Body</label><textarea class="field min-h-48" id="body" name="body">{{ old('body', $page->body) }}</textarea></div>
        <div><label class="label" for="meta_title">Meta title</label><input class="field" id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
        <div><label class="label" for="meta_description">Meta description</label><textarea class="field" id="meta_description" name="meta_description">{{ old('meta_description', $page->meta_description) }}</textarea></div>
        <div><label class="label" for="nav_group">Nav group</label><input class="field" id="nav_group" name="nav_group" value="{{ old('nav_group', $page->nav_group) }}"></div>
        <div><label class="label" for="sort_order">Sort order</label><input class="field" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $page->sort_order) }}" required></div>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="show_in_nav" value="1" @checked(old('show_in_nav', $page->show_in_nav))> Show in nav</label>
        <button class="btn-primary" type="submit">Save</button>
    </form>
@endsection