@extends('layouts.public')

@section('title', $siteName)
@section('meta_description', 'Join '.$siteName.'. Members pool funds into units backed by real estate, gold, oil, and other stable assets.')

@section('content')
    <x-banner-slider :banners="$banners" />

    <section class="mx-auto grid max-w-6xl items-end gap-8 px-4 py-12 md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)] md:py-16" data-reveal>
        <div class="min-w-0">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-gold-700">Member cooperative</p>
            <h1 class="mt-3 max-w-xl font-serif text-4xl font-semibold leading-tight text-navy-900 sm:text-5xl">Pool together. Hold real assets.</h1>
            <p class="mt-4 max-w-xl text-lg leading-8 text-stone-700">Register, make a confirmed payment, and receive units at the price on that day. The pool is aimed at property, gold, energy, and other holdings meant to keep their value.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                @if ($accountUrl)
                    <a href="{{ $accountUrl }}" class="btn-primary">Your account</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary">Join now</a>
                    <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                @endif
            </div>
        </div>
        <aside class="rounded-3xl bg-navy-900 p-6 text-cream shadow-lg">
            <p class="text-sm uppercase tracking-[0.14em] text-gold-400">Current unit price</p>
            <p class="mt-2 font-serif text-4xl">${{ $unitPrice }}</p>
            <p class="mt-4 text-sm leading-6 text-cream/80">The first payment must be at least ${{ $minimum }} USD. Units equal the amount paid divided by the price at that moment, rounded down. A later price change does not rewrite old payments.</p>
            <a href="{{ route('site.membership') }}" class="mt-4 inline-flex min-h-11 items-center text-sm font-semibold text-gold-400">How units are counted</a>
        </aside>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-16" data-reveal>
        <h2 class="font-serif text-3xl text-navy-900 sm:text-4xl">How it works</h2>
        <ol class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['01', 'Register', 'Create an account. An invite link stores the referral code for 30 days.'],
                ['02', 'Pay', 'Send at least the minimum through Flutterwave. The charge is checked on our server.'],
                ['03', 'Become a member', 'A confirmed first payment turns a pending account active and credits units.'],
                ['04', 'Earn returns', 'Buy more units any time. The pool is held for member returns, which are not guaranteed.'],
            ] as [$step, $heading, $copy])
                <li class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <p class="text-sm font-semibold text-gold-700">{{ $step }}</p>
                    <h3 class="mt-2 font-serif text-2xl text-navy-900">{{ $heading }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-700">{{ $copy }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="bg-white py-16" data-reveal>
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="font-serif text-3xl text-navy-900 sm:text-4xl">Investment areas</h2>
                <a href="{{ route('site.investments') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-navy-800">See the full picture</a>
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @forelse ($assets as $asset)
                    <a href="{{ \App\Support\SitePages::url($asset) }}" class="block rounded-3xl bg-cream p-5 ring-1 ring-stone-200">
                        <h3 class="font-serif text-2xl text-navy-900">{{ $asset->title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-stone-700">{{ $asset->excerpt }}</p>
                    </a>
                @empty
                    @foreach (['Real estate', 'Gold', 'Oil and energy', 'Other stable assets'] as $label)
                        <article class="rounded-3xl bg-cream p-5 ring-1 ring-stone-200">
                            <h3 class="font-serif text-2xl text-navy-900">{{ $label }}</h3>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-4 px-4 py-16 sm:grid-cols-3" data-reveal aria-label="Cooperative figures">
        @foreach ([['Members', $stats['members']], ['Units issued', $stats['units']], ['Asset areas', $stats['assets']]] as [$label, $value])
            <article class="rounded-3xl bg-navy-900 p-6 text-cream" x-data="statCount({{ (int) $value }})">
                <p class="font-serif text-4xl">
                    <span class="sr-only">{{ number_format((int) $value) }}</span>
                    <span aria-hidden="true" x-text="label">{{ number_format((int) $value) }}</span>
                </p>
                <p class="mt-2 text-sm text-gold-400">{{ $label }}</p>
            </article>
        @endforeach
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-16" data-reveal>
        <h2 class="font-serif text-3xl text-navy-900 sm:text-4xl">Why members join</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['A price that stays on the record', 'Every payment stores the unit price used that day. History is not recalculated when the board changes the price.'],
                ['One ledger, no edits', 'Units are added in an append-only ledger. Balances are the sum of those rows.'],
                ['A bonus for a real member', 'You receive '.$referralBonus.' units when someone you invite makes a confirmed first payment. A pending signup does not pay the bonus.'],
            ] as [$heading, $copy])
                <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <h3 class="font-serif text-2xl text-navy-900">{{ $heading }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-700">{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-navy-900 py-16 text-cream" data-reveal>
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-gold-400">Referral program</p>
                <h2 class="mt-3 font-serif text-3xl sm:text-4xl">Earn {{ $referralBonus }} units for each new active member</h2>
                <p class="mt-4 max-w-xl text-sm leading-7 text-cream/80">Share your link. The bonus is written once, after Flutterwave confirms that person's first payment. It is not paid when they only create an account, and it is not paid twice.</p>
                <a href="{{ route('site.referral-program') }}" class="btn-gold mt-6">Read the rules</a>
            </div>
            <ol class="space-y-3 text-sm leading-6">
                <li class="rounded-2xl bg-white/10 p-4">1. Your link looks like /register?ref=YOURCODE.</li>
                <li class="rounded-2xl bg-white/10 p-4">2. The code stays in a cookie for 30 days if they browse first.</li>
                <li class="rounded-2xl bg-white/10 p-4">3. Their first confirmed payment activates them and credits you.</li>
            </ol>
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-3xl px-4 py-16" data-reveal aria-roledescription="carousel" aria-label="Member notes" x-data="testimonialSlider({{ $testimonials->count() }})" @mouseenter="paused = true" @mouseleave="paused = false" @touchstart.passive="onTouchStart($event)" @touchend.passive="onTouchEnd($event)">
            <h2 class="text-center font-serif text-3xl text-navy-900">From the membership</h2>
            <div class="mt-6">
                @foreach ($testimonials as $index => $note)
                    <blockquote class="rounded-3xl bg-white p-6 ring-1 ring-stone-200" x-show="index === {{ $index }}" :inert="index !== {{ $index }}">
                        <p class="text-lg leading-8 text-navy-900">“{{ $note->quote }}”</p>
                        <footer class="mt-4 text-sm font-semibold text-stone-600">{{ $note->name }} · {{ $note->role }}@if ($note->location), {{ $note->location }}@endif</footer>
                    </blockquote>
                @endforeach
            </div>
            @if ($testimonials->count() > 1)
                <div class="mt-4 flex justify-center gap-2">
                    @foreach ($testimonials as $index => $note)
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center" @click="index = {{ $index }}" :aria-current="index === {{ $index }} ? 'true' : false" aria-label="Show note {{ $index + 1 }}">
                            <span class="h-2.5 w-2.5 rounded-full" :class="index === {{ $index }} ? 'bg-navy-900' : 'bg-stone-300'"></span>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-4 pb-16" data-reveal>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="font-serif text-3xl text-navy-900">Latest news</h2>
            <a href="{{ route('news.index') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-navy-800">All news</a>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($news as $item)
                @php
                    $isPost = $item instanceof \App\Modules\Cms\Models\Post;
                    $summary = $item->excerpt ?? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $item->body) ?? '', 140);
                @endphp
                <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gold-700">{{ $item->published_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
                    <h3 class="mt-2 font-serif text-2xl text-navy-900">
                        @if ($isPost)
                            <a class="hover:underline" href="{{ route('news.show', $item) }}">{{ $item->title }}</a>
                        @else
                            {{ $item->title }}
                        @endif
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-stone-700">{{ $summary }}</p>
                </article>
            @empty
                <p class="text-stone-600">News from the cooperative will appear here.</p>
            @endforelse
        </div>
        @if ($meeting)
            <p class="mt-6 text-sm text-stone-700">Next gathering: <a class="inline-flex min-h-11 items-center font-semibold text-navy-800" href="{{ route('events.index') }}">{{ $meeting->title }}</a>, {{ $meeting->starts_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}.</p>
        @endif
    </section>

    @if ($faqs->isNotEmpty())
        <section class="mx-auto max-w-3xl px-4 pb-16" data-reveal x-data="faqList">
            <h2 class="font-serif text-3xl text-navy-900">Questions members ask</h2>
            <div class="mt-6 divide-y divide-stone-200 rounded-3xl bg-white ring-1 ring-stone-200">
                @foreach ($faqs as $faq)
                    <div>
                        <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 px-5 py-4 text-left font-semibold" @click="toggle({{ $faq->id }})" :aria-expanded="open === {{ $faq->id }} ? 'true' : 'false'">
                            <span>{{ $faq->question }}</span>
                            <span aria-hidden="true" x-text="open === {{ $faq->id }} ? '−' : '+'"></span>
                        </button>
                        <div class="px-5 pb-4 text-sm leading-6 text-stone-700" x-show="open === {{ $faq->id }}">{{ $faq->answer }}</div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('faq') }}" class="mt-4 inline-flex min-h-11 items-center text-sm font-semibold text-navy-800">Read all questions</a>
        </section>
    @endif

    <section class="bg-gold-100">
        <div class="mx-auto flex max-w-6xl flex-col items-start gap-4 px-4 py-14 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-serif text-3xl text-navy-900">Ready to hold a unit?</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-stone-700">Create an account, then complete the first payment. Membership starts when that payment is confirmed.</p>
            </div>
            @if ($accountUrl)
                <a href="{{ $accountUrl }}" class="btn-primary">Your account</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary">Join now</a>
            @endif
        </div>
    </section>
@endsection
