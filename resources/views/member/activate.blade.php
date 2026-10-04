@extends('layouts.member')

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-700">Activation</p>
    <h1 class="mt-2 font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Make your first payment</h1>
    <div class="mt-4 max-w-2xl space-y-3 text-stone-700">
        <p>Membership starts when Flutterwave confirms this payment. Until then the account stays pending and no units are credited.</p>
        <p>The USD amount below is what buys units. The price on this page is saved with the payment, so a later price change does not rewrite it. Your member number is issued when the payment is confirmed.</p>
        <p>Current balance: {{ number_format($balance) }} units.</p>
    </div>

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
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-serif text-2xl">Recent payments</h2>
                <a href="{{ route('member.payments.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">Full history</a>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ($payments as $payment)
                    <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="break-all font-semibold">{{ $payment->tx_ref }}</p>
                        <p class="mt-1 text-sm text-stone-700">${{ $payment->amount_usd }} · {{ number_format($payment->units_purchased) }} units · {{ $payment->status->value }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
