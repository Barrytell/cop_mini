@extends('layouts.public')

@section('title', 'Testimonials')
@section('meta_description', 'Notes from members about joining, paying, and holding units.')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">Testimonials</h1>
        <p class="mt-4 max-w-2xl text-lg text-stone-700">{{ $page?->excerpt ?: 'Members describe the first payment, the ledger, and inviting someone else.' }}</p>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @forelse ($testimonials as $note)
                <blockquote class="rounded-3xl bg-white p-6 ring-1 ring-stone-200">
                    <p class="text-lg leading-8 text-navy-900">“{{ $note->quote }}”</p>
                    <footer class="mt-4 text-sm font-semibold text-stone-600">{{ $note->name }} · {{ $note->role }}@if ($note->location), {{ $note->location }}@endif</footer>
                </blockquote>
            @empty
                <p class="text-stone-600">Member notes will appear here.</p>
            @endforelse
        </div>
    </div>
@endsection
