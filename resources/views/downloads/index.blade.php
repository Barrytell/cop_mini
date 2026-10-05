@extends('layouts.public')

@section('title', 'Downloads')
@section('meta_description', 'Membership guide and risk summary documents.')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">Downloads</h1>
        <p class="mt-4 text-lg text-stone-700">{{ $page?->excerpt ?: 'Documents you can keep. They describe membership and the risks of the pool.' }}</p>
        <ul class="mt-8 space-y-3">
            @forelse ($downloads as $file)
                <li class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    @if ($href = public_file($file->file_path))
                        <a href="{{ $href }}" class="inline-flex min-h-11 items-center font-serif text-2xl text-navy-900" download>{{ $file->title }}</a>
                    @else
                        <p class="font-serif text-2xl text-navy-900">{{ $file->title }}</p>
                    @endif
                    @if ($file->description)
                        <p class="mt-2 text-sm leading-6 text-stone-700">{{ $file->description }}</p>
                    @endif
                    <p class="mt-2 text-xs text-stone-500">PDF · {{ number_format($file->file_size / 1024, 1) }} KB</p>
                </li>
            @empty
                <li class="text-stone-600">Documents will be listed here.</li>
            @endforelse
        </ul>
    </div>
@endsection
