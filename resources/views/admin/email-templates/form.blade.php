@extends('layouts.admin')
@section('title', 'Edit template')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $template->name }}</h1>
    <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <div><label class="label" for="name">Name</label><input class="field" id="name" name="name" value="{{ old('name', $template->name) }}" required></div>
        <div><label class="label" for="subject">Subject</label><input class="field" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required></div>
        <div><label class="label" for="body">Body</label><textarea class="field min-h-48" id="body" name="body" required>{{ old('body', $template->body) }}</textarea></div>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))> Active</label>
        <button class="btn-primary" type="submit">Save</button>
    </form>
@endsection