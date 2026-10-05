@extends('layouts.public')

@section('title', $page?->meta_title ?: 'FAQ')
@section('meta_description', $page?->meta_description ?: 'Answers about units, payments, referrals, and membership.')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">{{ $page?->title ?: 'FAQ' }}</h1>
        @if ($page?->excerpt)
            <p class="mt-4 text-lg leading-8 text-stone-700">{{ $page->excerpt }}</p>
        @endif
        <div class="mt-8 divide-y divide-stone-200 rounded-3xl bg-white ring-1 ring-stone-200" x-data="faqList">
            @forelse ($faqs as $faq)
                <div>
                    <h2>
                        <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 px-5 py-4 text-left font-semibold" @click="toggle({{ $faq->id }})" :aria-expanded="open === {{ $faq->id }} ? 'true' : 'false'">
                            <span>{{ $faq->question }}</span>
                            <span aria-hidden="true" x-text="open === {{ $faq->id }} ? '−' : '+'"></span>
                        </button>
                    </h2>
                    <div class="px-5 pb-4 text-sm leading-6 text-stone-700" x-show="open === {{ $faq->id }}">{{ $faq->answer }}</div>
                </div>
            @empty
                <p class="p-5 text-stone-600">Questions will be published here.</p>
            @endforelse
        </div>
    </div>
@endsection
