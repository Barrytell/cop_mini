@extends('layouts.public')

@section('title', 'Gallery')
@section('meta_description', 'Photographs and studies of the asset areas the cooperative follows.')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">Gallery</h1>
        <p class="mt-4 max-w-2xl text-lg text-stone-700">{{ $page?->excerpt ?: 'A look at the kinds of holdings the pool is built to follow.' }}</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @forelse ($items as $item)
                <figure class="overflow-hidden rounded-3xl bg-white ring-1 ring-stone-200">
                    @if ($src = public_file($item->image_path))
                        <img src="{{ $src }}" alt="{{ $item->title }}" class="h-56 w-full object-cover" width="800" height="600" loading="lazy" decoding="async">
                    @endif
                    <figcaption class="p-4">
                        <p class="font-semibold text-navy-900">{{ $item->title }}</p>
                        @if ($item->caption)
                            <p class="mt-1 text-sm leading-6 text-stone-700">{{ $item->caption }}</p>
                        @endif
                    </figcaption>
                </figure>
            @empty
                <p class="text-stone-600">Gallery images will appear here.</p>
            @endforelse
        </div>
    </div>
@endsection
