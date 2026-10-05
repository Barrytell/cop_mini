@extends('layouts.member')

@section('title', 'New message')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Message the cooperative</h1>
    <form method="POST" action="{{ route('member.support.store') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        <div>
            <label class="label" for="subject">Subject</label>
            <input class="field" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="120">
        </div>
        <div>
            <label class="label" for="body">Message</label>
            <textarea class="field min-h-40" id="body" name="body" required maxlength="5000">{{ old('body') }}</textarea>
        </div>
        <button type="submit" class="btn-primary">Send</button>
    </form>
@endsection
