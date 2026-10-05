@extends('layouts.member')

@section('title', 'Referrals')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Referral center</h1>
    <p class="mt-2 max-w-2xl text-stone-700">Share your link. The bonus is credited once, after the person you invite makes a confirmed first payment. Pending signups do not pay the bonus.</p>

    <section class="mt-6 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        <p class="text-sm font-semibold text-stone-600">Your link</p>
        <p class="mt-2 break-all font-semibold text-forest-900">{{ $link }}</p>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap" x-data="copyText(@js($link))">
            <button type="button" class="btn-primary" @click="copy()" x-text="copied ? 'Copied' : 'Copy link'"></button>
            @php
                $text = rawurlencode($shareText.' '.$link);
                $url = rawurlencode($link);
            @endphp
            <a class="btn-ghost" href="https://wa.me/?text={{ $text }}" rel="noopener noreferrer">WhatsApp</a>
            <a class="btn-ghost" href="https://www.facebook.com/sharer/sharer.php?u={{ $url }}" rel="noopener noreferrer">Facebook</a>
            <a class="btn-ghost" href="https://twitter.com/intent/tweet?url={{ $url }}&text={{ rawurlencode($shareText) }}" rel="noopener noreferrer">X</a>
            <a class="btn-ghost" href="https://t.me/share/url?url={{ $url }}&text={{ rawurlencode($shareText) }}" rel="noopener noreferrer">Telegram</a>
            <a class="btn-ghost" href="mailto:?subject={{ rawurlencode('Join '.$siteName) }}&body={{ $text }}">Email</a>
        </div>
        <img src="{{ route('member.referrals.qr') }}" alt="QR code for your referral link" class="mt-6 h-44 w-44 rounded-2xl bg-white" width="176" height="176">
        <p class="mt-4 text-sm text-stone-600">Bonus units earned: {{ number_format($bonusUnits) }}</p>
    </section>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">People you referred</h2>
        <div class="mt-4 space-y-3">
            @forelse ($referrals as $referral)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $referral->referred?->name ?? 'Former member' }}</p>
                    <p class="mt-1 text-sm text-stone-700">{{ $referral->status->value === 'rewarded' ? 'Active' : 'Pending' }}</p>
                    <p class="mt-1 text-sm text-stone-600">Bonus awarded: {{ number_format($referral->bonus_units) }} units</p>
                </article>
            @empty
                <p class="text-stone-600">No referrals yet.</p>
            @endforelse
        </div>
        <div class="mt-6 overflow-x-auto">{{ $referrals->links() }}</div>
    </section>
@endsection
