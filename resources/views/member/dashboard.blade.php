@extends('layouts.member')

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-700">{{ auth()->user()->member_no }}</p>
    <h1 class="mt-2 font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Hello, {{ auth()->user()->name }}</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <article class="rounded-3xl bg-forest-900 p-5 text-cream">
            <p class="text-sm uppercase tracking-[0.14em] text-gold-400">Unit balance</p>
            <p class="mt-2 font-serif text-4xl">{{ number_format($balance) }}</p>
        </article>
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <p class="text-sm font-semibold text-stone-600">Your referral link</p>
            <p class="mt-2 break-all text-sm font-semibold text-forest-900">{{ $referralLink }}</p>
            <p class="mt-2 text-sm text-stone-600">Code {{ auth()->user()->referral_code }}. The bonus is credited once, after their first payment is confirmed.</p>
        </article>
    </div>

    <section class="mt-8 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        <h2 class="font-serif text-2xl">Buy units</h2>
        <p class="mt-2 text-stone-700">Add units at the current price of ${{ rtrim(rtrim($unitPrice, '0'), '.') }}.</p>
        <a href="{{ route('member.units.buy') }}" class="btn-primary mt-4">Buy units</a>
    </section>

    @if ($notifications->isNotEmpty())
        <section class="mt-8">
            <h2 class="font-serif text-2xl">Notifications</h2>
            <div class="mt-4 space-y-3">
                @foreach ($notifications as $notification)
                    <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="font-semibold">{{ $notification->data['tx_ref'] ?? 'Update' }}</p>
                        <p class="mt-1 text-sm text-stone-700">
                            @if (isset($notification->data['units']))
                                {{ number_format((int) $notification->data['units']) }} units
                                @if (! empty($notification->data['amount_usd']))
                                    from ${{ $notification->data['amount_usd'] }}
                                @endif
                            @elseif (isset($notification->data['bonus_units']))
                                Referral bonus of {{ number_format((int) $notification->data['bonus_units']) }} units
                            @else
                                {{ $notification->type }}
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-stone-500">{{ $notification->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Ledger</h2>
        <div class="mt-4 space-y-3">
            @forelse ($ledger as $entry)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-semibold">{{ str_replace('_', ' ', $entry->type->value) }}</p>
                        <p class="font-semibold {{ $entry->units < 0 ? 'text-red-700' : 'text-forest-800' }}">{{ $entry->units > 0 ? '+' : '' }}{{ number_format($entry->units) }}</p>
                    </div>
                    @if ($entry->note)
                        <p class="mt-1 break-words text-sm text-stone-600">{{ $entry->note }}</p>
                    @endif
                    <p class="mt-1 text-xs text-stone-500">{{ $entry->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                </article>
            @empty
                <p class="text-stone-600">No units yet.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">People you referred</h2>
        <div class="mt-4 space-y-3">
            @forelse ($referrals as $referral)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $referral->referred?->name ?? 'Removed member' }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $referral->status->value }} @if ($referral->status->value === 'rewarded') · {{ $referral->bonus_units }} units @endif</p>
                </article>
            @empty
                <p class="text-stone-600">No referrals yet.</p>
            @endforelse
        </div>
    </section>
@endsection
