@extends('layouts.admin')
@section('title', $announcement->exists ? 'Edit announcement' : 'Create announcement')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $announcement->exists ? 'Edit' : 'Create' }} announcement</h1>
    <form method="POST" enctype="multipart/form-data" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="mt-6 max-w-3xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @if($announcement->exists) @method('PUT') @endif
        <div>
            <label class="label" for="title">Title</label>
            <input class="field" id="title" name="title" value="{{ old('title', $announcement->title) }}" required maxlength="160">
        </div>
        <div>
            <label class="label" for="body">Body</label>
            <textarea class="field min-h-48" id="body" name="body" required>{{ old('body', $announcement->body) }}</textarea>
        </div>
        <div>
            <label class="label" for="audience">Audience</label>
            <select class="field" id="audience" name="audience" required>
                @foreach (['all','active','pending','selected'] as $audience)
                    <option value="{{ $audience }}" @selected(old('audience', $announcement->audience) === $audience)>{{ $audience }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="audience_user_ids">Selected members</label>
            <select class="field min-h-40" id="audience_user_ids" name="audience_user_ids[]" multiple>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected(in_array($member->id, old('audience_user_ids', $announcement->audience_user_ids ?? []), true))>{{ $member->name }} · {{ $member->email }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="scheduled_for">Schedule publish</label>
            <input class="field" type="datetime-local" id="scheduled_for" name="scheduled_for" value="{{ old('scheduled_for', optional($announcement->scheduled_for)->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label class="label" for="attachment">Attachment</label>
            <input class="field" type="file" id="attachment" name="attachment">
        </div>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned))> Pin</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $announcement->is_published))> Published</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="send_email" value="1" @checked(old('send_email', $announcement->send_email))> Send email</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="send_notification" value="1" @checked(old('send_notification', $announcement->send_notification ?? true))> In-app notification</label>
        <div class="flex flex-wrap gap-2">
            <button class="btn-primary" type="submit">Save</button>
            @if($announcement->exists)
                <button class="btn-secondary" form="delete-announcement" type="submit">Delete</button>
            @endif
        </div>
    </form>
    @if($announcement->exists)
        <form id="delete-announcement" method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">@csrf @method('DELETE')</form>
    @endif
@endsection