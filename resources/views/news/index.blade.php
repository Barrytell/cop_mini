@extends('layouts.public')

@section('title', $current ? $current->name : 'News')
@section('meta_description', $current?->description ?: 'Notes from the cooperative on assets, membership, and meetings.')
@section('canonical', $current ? route('news.index', ['category' => $current->slug]) : route('news.index'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">News</h1>
        <p class="mt-3 max-w-2xl text-lg text-stone-700">Reports on the pool, membership, and the assets the cooperative holds.</p>
        <div class="mt-6 flex gap-2 overflow-x-auto pb-2">
            <a href="{{ route('news.index') }}" class="inline-flex min-h-11 shrink-0 items-center rounded-full px-4 text-sm font-semibold {{ $current ? 'bg-white ring-1 ring-stone-300' : 'bg-navy-900 text-cream' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('news.index', ['category' => $category->slug]) }}" class="inline-flex min-h-11 shrink-0 items-center rounded-full px-4 text-sm font-semibold {{ $current?->is($category) ? 'bg-navy-900 text-cream' : 'bg-white ring-1 ring-stone-300' }}">{{ $category->name }}</a>
            @endforeach
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <article class="overflow-hidden rounded-3xl bg-white ring-1 ring-stone-200">
                    @if ($cover = public_file($post->cover_path))
                        <img src="{{ $cover }}" alt="" class="h-44 w-full object-cover" width="640" height="360" loading="lazy" decoding="async">
                    @endif
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gold-700">{{ $post->category?->name }} · {{ $post->published_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
                        <h2 class="mt-2 font-serif text-2xl text-navy-900"><a href="{{ route('news.show', $post) }}">{{ $post->title }}</a></h2>
                        <p class="mt-2 text-sm leading-6 text-stone-700">{{ $post->excerpt }}</p>
                    </div>
                </article>
            @empty
                <p class="text-stone-600">No stories in this category yet.</p>
            @endforelse
        </div>
        <div class="mt-8 overflow-x-auto">{{ $posts->links() }}</div>
    </div>
@endsection
