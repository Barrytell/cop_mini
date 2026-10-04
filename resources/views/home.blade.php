@extends('layouts.public')

@section('content')
    <section class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-2 md:items-center md:py-16">
        <div class="min-w-0">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-gold-700">Member cooperative</p>
            <h1 class="mt-3 font-serif text-4xl font-semibold leading-tight text-forest-900 sm:text-5xl">{{ $banner->title ?? 'Pool together. Hold real assets.' }}</h1>
            <p class="mt-4 max-w-xl text-lg text-stone-700">{{ $banner->subtitle ?? 'Members buy units. The cooperative puts that capital into real estate, gold, oil, and other holdings meant to stay steady.' }}</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-primary">Become a member</a>
                <a href="{{ ($how = $navPages->firstWhere('slug', 'how-it-works')) ? route('pages.show', $how) : route('register') }}" class="btn-ghost">How units work</a>
            </div>
        </div>
        <div class="rounded-3xl bg-forest-900 p-6 text-cream shadow-lg">
            <p class="text-sm uppercase tracking-[0.14em] text-gold-400">Current unit price</p>
            <p class="mt-2 font-serif text-4xl">${{ $unitPrice }}</p>
            <p class="mt-4 text-sm text-cream/80">Minimum first payment is ${{ $minimum }} USD. Units equal the amount paid divided by the price at that moment, rounded down. Later price changes do not rewrite old payments.</p>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-4 px-4 pb-12 md:grid-cols-3">
        @foreach ([
            ['01', 'Register', 'Create an account. A referral code is optional and is saved from the invite link.'],
            ['02', 'Pay', 'The first confirmed payment turns a pending account into an active member and credits units.'],
            ['03', 'Hold', 'Buy more units any time. Referral bonuses post once, when the person you invited becomes active.'],
        ] as [$step, $heading, $copy])
            <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                <p class="text-sm font-semibold text-gold-700">{{ $step }}</p>
                <h2 class="mt-2 font-serif text-2xl">{{ $heading }}</h2>
                <p class="mt-2 text-stone-700">{{ $copy }}</p>
            </article>
        @endforeach
    </section>

    <section class="mx-auto grid max-w-6xl gap-8 px-4 pb-16 lg:grid-cols-2">
        <div>
            <h2 class="font-serif text-3xl">Announcements</h2>
            <div class="mt-4 space-y-4">
                @forelse ($announcements as $announcement)
                    <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                        <h3 class="text-lg font-semibold">{{ $announcement->title }}</h3>
                        <p class="mt-2 whitespace-pre-wrap text-stone-700">{{ $announcement->body }}</p>
                    </article>
                @empty
                    <p class="text-stone-600">No announcements yet.</p>
                @endforelse
            </div>
        </div>
        <div>
            <h2 class="font-serif text-3xl">Next meeting</h2>
            @if ($meeting)
                <article class="mt-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                    <h3 class="text-lg font-semibold">{{ $meeting->title }}</h3>
                    <p class="mt-2 text-stone-700">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A') }}</p>
                    @if ($meeting->location)
                        <p class="mt-1 text-stone-700">{{ $meeting->location }}</p>
                    @endif
                    @if ($meeting->description)
                        <p class="mt-3 whitespace-pre-wrap text-stone-700">{{ $meeting->description }}</p>
                    @endif
                    @if ($joinUrl = safe_url($meeting->meeting_url))
                        <a href="{{ $joinUrl }}" class="mt-4 inline-flex min-h-11 items-center break-all font-semibold text-forest-800" rel="noopener noreferrer">Join link</a>
                    @endif
                </article>
            @else
                <p class="mt-4 text-stone-600">No meeting is scheduled.</p>
            @endif
        </div>
    </section>
@endsection
