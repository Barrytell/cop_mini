@extends('layouts.member')

@section('title', 'Dashboard')

@section('banner')
    <x-banner-slider :banners="$banners" />
@endsection

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-700">Welcome back</p>
            <h1 class="mt-2 break-words font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">{{ auth()->user()->name }}</h1>
            <p class="mt-2 text-stone-700">{{ auth()->user()->member_no }}</p>
        </div>
        <span class="inline-flex min-h-11 items-center rounded-full bg-forest-900 px-4 text-sm font-semibold text-cream">Active member</span>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-3">
        @foreach ([
            ['Units owned', number_format($units)],
            ['Unit price', '$'.$unitPriceLabel],
            ['Estimated value', '$'.$contribution],
            ['Total paid', '$'.$totalPaid],
            ['Referrals', number_format($referralCount)],
            ['Bonus units', number_format($bonusUnits)],
        ] as [$label, $value])
            <article class="rounded-3xl bg-white p-4 ring-1 ring-stone-200">
                <p class="text-sm text-stone-600">{{ $label }}</p>
                <p class="mt-2 break-all font-serif text-2xl text-forest-900 sm:text-3xl">{{ $value }}</p>
            </article>
        @endforeach
    </div>
    <p class="mt-3 text-sm text-stone-600">Add units at the current price of ${{ $unitPriceLabel }}.</p>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a href="{{ route('member.units.buy') }}" class="btn-primary">Buy more units</a>
        <button type="button" class="btn-ghost" x-data="copyText(@js($referralLink))" @click="copy()" x-text="copied ? 'Link copied' : 'Copy referral link'"></button>
        <a href="{{ route('member.announcements.index') }}" class="btn-ghost">View announcements</a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section>
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl">Announcements</h2>
                <a href="{{ route('member.announcements.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">All</a>
            </div>
            <div class="mt-3 space-y-3">
                @forelse ($announcements as $announcement)
                    <a href="{{ route('member.announcements.show', $announcement) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="font-semibold">{{ $announcement->title }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $announcement->published_at?->timezone(config('app.timezone'))->format('M j, Y') }}</p>
                    </a>
                @empty
                    <p class="text-stone-600">No announcements yet.</p>
                @endforelse
            </div>
        </section>
        <section>
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl">Upcoming meetings</h2>
                <a href="{{ route('member.meetings.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">All</a>
            </div>
            <div class="mt-3 space-y-3">
                @forelse ($meetings as $meeting)
                    <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="font-semibold">{{ $meeting->title }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $meeting->starts_at->timezone(config('app.timezone'))->format('D, M j, Y g:i A') }}</p>
                        @if ($meeting->location)
                            <p class="mt-1 text-sm text-stone-700">{{ $meeting->location }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-stone-600">No meeting is scheduled.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-serif text-2xl">Recent transactions</h2>
            <a href="{{ route('member.ledger.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">Statement</a>
        </div>
        <div class="mt-3 space-y-3 md:hidden">
            @forelse ($ledger as $entry)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold {{ $entry->units < 0 ? 'text-red-700' : 'text-forest-800' }}">{{ $entry->units > 0 ? '+' : '' }}{{ number_format($entry->units) }}</p>
                    <p class="mt-1 text-sm">{{ str_replace('_', ' ', $entry->type->value) }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $entry->note }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ $entry->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                </article>
            @empty
                <p class="text-stone-600">No transactions yet.</p>
            @endforelse
        </div>
        <div class="mt-3 hidden overflow-x-auto rounded-2xl bg-white ring-1 ring-stone-200 md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 text-stone-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Units</th>
                        <th class="px-4 py-3 font-semibold">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ledger as $entry)
                        <tr class="border-b border-stone-100 last:border-0">
                            <td class="px-4 py-3">{{ $entry->created_at?->timezone(config('app.timezone'))->format('M j, Y') }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $entry->type->value) }}</td>
                            <td class="px-4 py-3 font-semibold {{ $entry->units < 0 ? 'text-red-700' : 'text-forest-800' }}">{{ $entry->units > 0 ? '+' : '' }}{{ number_format($entry->units) }}</td>
                            <td class="px-4 py-3">{{ $entry->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if ($referrals->isNotEmpty())
        <section class="mt-8">
            <h2 class="font-serif text-2xl">People you referred</h2>
            <div class="mt-3 space-y-3">
                @foreach ($referrals as $referral)
                    <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="font-semibold">{{ $referral->referred?->name ?? 'Former member' }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $referral->status->value }}@if ($referral->bonus_units) · {{ number_format($referral->bonus_units) }} units @endif</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
