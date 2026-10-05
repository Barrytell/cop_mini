@extends('layouts.public')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: ($page->excerpt ?: $page->title))
@section('canonical', \App\Support\SitePages::url($page))
@if ($page->og_image)
    @section('og_image', asset($page->og_image))
@endif

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-gold-700">{{ $siteName }}</p>
        <h1 class="mt-3 font-serif text-4xl font-semibold text-navy-900 sm:text-5xl">{{ $page->title }}</h1>
        @if ($page->excerpt)
            <p class="mt-4 text-lg leading-8 text-stone-700">{{ $page->excerpt }}</p>
        @endif

        @if ($page->template === 'membership')
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <p class="rounded-3xl bg-navy-900 p-5 text-cream"><span class="block text-sm text-gold-400">Unit price</span><span class="mt-1 block font-serif text-3xl">${{ $unitPrice }}</span></p>
                <p class="rounded-3xl bg-white p-5 ring-1 ring-stone-200"><span class="block text-sm text-stone-600">Minimum first payment</span><span class="mt-1 block font-serif text-3xl text-navy-900">${{ $minimum }}</span></p>
            </div>
        @endif

        @if ($page->template === 'referral')
            <p class="mt-6 rounded-3xl bg-gold-100 p-5 text-navy-900">The current bonus is <strong>{{ $referralBonus }} units</strong>, paid once when the person you invite becomes an active member.</p>
        @endif

        <div class="cms-body">{!! \App\Support\CmsText::toHtml($page->body) !!}</div>

        @if ($page->template === 'team' && $team->isNotEmpty())
            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($team as $person)
                    <section class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                        @if ($portrait = public_file($person->image_path))
                            <img src="{{ $portrait }}" alt="" class="h-16 w-16 rounded-full object-cover" width="64" height="64" loading="lazy" decoding="async">
                        @endif
                        <h2 class="mt-3 font-serif text-2xl text-navy-900">{{ $person->name }}</h2>
                        <p class="text-sm font-semibold text-gold-700">{{ $person->role }}</p>
                        <p class="mt-2 text-sm leading-6 text-stone-700">{{ $person->bio }}</p>
                    </section>
                @endforeach
            </div>
        @endif

        @if (in_array($page->template, ['investments', 'asset'], true) && $assets->isNotEmpty())
            <div class="mt-10 grid gap-3">
                @foreach ($assets as $asset)
                    <a href="{{ \App\Support\SitePages::url($asset) }}" class="block rounded-3xl bg-white p-5 ring-1 ring-stone-200 {{ $asset->is($page) ? 'ring-gold-500' : '' }}">
                        <h2 class="font-serif text-2xl text-navy-900">{{ $asset->title }}</h2>
                        <p class="mt-1 text-sm leading-6 text-stone-700">{{ $asset->excerpt }}</p>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($page->template === 'faq' && $faqs->isNotEmpty())
            <div class="mt-8 divide-y divide-stone-200 rounded-3xl bg-white ring-1 ring-stone-200" x-data="faqList">
                @foreach ($faqs as $faq)
                    <div>
                        <h2>
                            <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 px-5 py-4 text-left font-semibold" @click="toggle({{ $faq->id }})" :aria-expanded="open === {{ $faq->id }} ? 'true' : 'false'">
                                <span>{{ $faq->question }}</span>
                                <span aria-hidden="true" x-text="open === {{ $faq->id }} ? '−' : '+'"></span>
                            </button>
                        </h2>
                        <div class="px-5 pb-4 text-sm leading-6 text-stone-700" x-show="open === {{ $faq->id }}">{{ $faq->answer }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </article>
@endsection
