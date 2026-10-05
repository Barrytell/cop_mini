@extends('layouts.admin')
@section('title', $message->subject)
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $message->subject }}</h1>
    <p class="mt-2 text-stone-600">{{ $message->name }} · <a class="underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a> · {{ $message->phone }}</p>
    <article class="mt-6 whitespace-pre-wrap rounded-3xl bg-white p-5 ring-1 ring-stone-200">{{ $message->body }}</article>
    <form method="POST" action="{{ route('admin.contact.destroy', $message) }}" class="mt-6" onsubmit="return confirm('Delete message?')">@csrf @method('DELETE')<button class="btn-secondary" type="submit">Delete</button></form>
@endsection