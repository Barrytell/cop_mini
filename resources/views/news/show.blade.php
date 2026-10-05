@extends('layouts.public')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('canonical', route('news.show', $post))
@if ($cover = public_file($post->cover_path))
    @section('og_image', $cover)
@endif

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('news.index') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-navy-800">All news</a>
        <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em] text-gold-700">{{ $post->category?->name }} · {{ $post->published_at?->timezone(config('app.timezone'))->format('F j, Y') }}</p>
        <h1 class="mt-3 font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-4 text-lg leading-8 text-stone-700">{{ $post->excerpt }}</p>
        @if ($cover)
            <img src="{{ $cover }}" alt="" class="mt-6 w-full rounded-3xl object-cover" width="1200" height="675" decoding="async">
        @endif
        <div class="cms-body">{!! \App\Support\CmsText::toHtml($post->body) !!}</div>
    </article>
    @if ($related->isNotEmpty())
        <aside class="mx-auto grid max-w-6xl gap-4 px-4 pb-16 md:grid-cols-3">
            @foreach ($related as $item)
                <a href="{{ route('news.show', $item) }}" class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <p class="font-serif text-xl text-navy-900">{{ $item->title }}</p>
                </a>
            @endforeach
        </aside>
    @endif
@endsection
