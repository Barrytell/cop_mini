@extends('layouts.member')

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-700">{{ auth()->user()->status->value === 'pending' ? 'Activation' : 'Top up' }}</p>
    <h1 class="mt-2 font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">
        {{ auth()->user()->status->value === 'pending' ? 'Make your first payment' : 'Buy more units' }}
    </h1>
    <p class="mt-3 max-w-2xl text-stone-700">Member {{ auth()->user()->member_no }}. Current balance: {{ number_format($balance) }} units.</p>

    @unless (auth()->user()->hasVerifiedEmail())
        <div class="mt-6 rounded-3xl border border-gold-400 bg-gold-100 p-4">
            <p class="font-semibold">Confirm your email when you can.</p>
            <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn-ghost">Resend verification email</button>
            </form>
        </div>
    @endunless

    <section class="mt-6 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @include('member.partials.buy-units')
    </section>

    @if ($payments->isNotEmpty())
        <section class="mt-8">
            <h2 class="font-serif text-2xl">Your payments</h2>
            <div class="mt-4 space-y-3">
                @foreach ($payments as $payment)
                    <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="break-all font-semibold">{{ $payment->tx_ref }}</p>
                        <p class="mt-1 text-sm text-stone-700">${{ $payment->amount_usd }} · {{ $payment->units_purchased }} units at ${{ $payment->unit_price_snapshot }} · {{ $payment->status->value }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
