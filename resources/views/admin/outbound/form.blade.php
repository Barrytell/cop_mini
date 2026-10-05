@extends('layouts.admin')
@section('title', 'Compose message')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Compose bulk message</h1>
    <form method="POST" action="{{ route('admin.outbound.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        <div>
            <label class="label" for="channel">Channel</label>
            <select class="field" id="channel" name="channel" required>
                <option value="email">Email</option>
                <option value="sms">SMS-ready</option>
            </select>
        </div>
        <div><label class="label" for="subject">Subject</label><input class="field" id="subject" name="subject" maxlength="160"></div>
        <div><label class="label" for="body">Body</label><textarea class="field min-h-40" id="body" name="body" required></textarea></div>
        <div>
            <label class="label" for="audience">Audience</label>
            <select class="field" id="audience" name="audience" required>
                @foreach (['all','active','pending'] as $audience)
                    <option value="{{ $audience }}">{{ $audience }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary" type="submit">Queue send</button>
    </form>
@endsection