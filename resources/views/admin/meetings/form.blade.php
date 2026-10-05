@extends('layouts.admin')
@section('title', $meeting->exists ? 'Edit meeting' : 'Create meeting')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $meeting->exists ? 'Edit' : 'Create' }} meeting</h1>
    <form method="POST" action="{{ $meeting->exists ? route('admin.meetings.update', $meeting) : route('admin.meetings.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @if($meeting->exists) @method('PUT') @endif
        <div><label class="label" for="title">Title</label><input class="field" id="title" name="title" value="{{ old('title', $meeting->title) }}" required></div>
        <div><label class="label" for="description">Agenda</label><textarea class="field min-h-32" id="description" name="description">{{ old('description', $meeting->description) }}</textarea></div>
        <div><label class="label" for="location">Venue</label><input class="field" id="location" name="location" value="{{ old('location', $meeting->location) }}"></div>
        <div><label class="label" for="meeting_url">Online link</label><input class="field" id="meeting_url" name="meeting_url" type="url" value="{{ old('meeting_url', $meeting->meeting_url) }}"></div>
        <div><label class="label" for="starts_at">Starts</label><input class="field" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($meeting->starts_at)->format('Y-m-d\TH:i')) }}" required></div>
        <div><label class="label" for="ends_at">Ends</label><input class="field" id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($meeting->ends_at)->format('Y-m-d\TH:i')) }}"></div>
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status" required>
                @foreach (['scheduled','completed','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $meeting->status?->value ?? 'scheduled') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary" type="submit">Save</button>
    </form>
@endsection