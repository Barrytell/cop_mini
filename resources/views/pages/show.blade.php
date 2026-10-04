@extends('layouts.public')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="font-serif text-4xl font-semibold text-forest-900">{{ $page->title }}</h1>
        @if ($page->excerpt)
            <p class="mt-3 text-lg text-stone-700">{{ $page->excerpt }}</p>
        @endif
        <div class="mt-6 whitespace-pre-wrap text-base leading-7 text-ink">{{ $page->body }}</div>
    </article>
@endsection
