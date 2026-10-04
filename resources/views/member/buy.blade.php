@extends('layouts.member')

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-700">Top up</p>
    <h1 class="mt-2 font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Buy more units</h1>
    <p class="mt-3 max-w-2xl text-stone-700">Member {{ auth()->user()->member_no }}. Current balance: {{ number_format($balance) }} units. This purchase is recorded separately from your first payment.</p>

    <section class="mt-6 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @include('member.partials.buy-units')
    </section>
@endsection
