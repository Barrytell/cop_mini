@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-semibold sm:text-4xl">Dashboard</h1>
            <p class="mt-2 text-stone-600">Membership, revenue, and queue work at a glance.</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="label" for="from">From</label>
                <input class="field" type="date" id="from" name="from" value="{{ $from }}">
            </div>
            <div>
                <label class="label" for="to">To</label>
                <input class="field" type="date" id="to" name="to" value="{{ $to }}">
            </div>
            <button class="btn-primary" type="submit">Apply</button>
        </form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Total members' => $totalMembers,
            'Active' => $activeMembers,
            'Pending' => $pendingMembers,
            'Units issued' => $totalUnits,
            'Revenue today' => $revenueToday,
            'Revenue month' => $revenueMonth,
            'Revenue all-time' => $revenueAll,
            'Referral bonuses' => $referralBonuses,
        ] as $label => $value)
            <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                <p class="text-sm font-semibold text-stone-600">{{ $label }}</p>
                <p class="mt-2 font-serif text-3xl">{{ is_numeric($value) ? number_format((float) $value) : $value }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-8 grid gap-6 lg:grid-cols-2" x-data="{
        series: {{ Js::from($series) }},
        maxSignup() { return Math.max(1, ...this.series.map(d => d.signups)); },
        maxRevenue() { return Math.max(1, ...this.series.map(d => Number(d.revenue_raw || 0))); }
    }">
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Signups</h2>
            <div class="mt-4 flex h-40 items-end gap-1 overflow-x-auto">
                <template x-for="day in series" :key="day.day">
                    <div class="flex min-w-[10px] flex-1 flex-col items-center justify-end">
                        <div class="w-full rounded-t bg-forest-700" :style="`height:${Math.max(4, (day.signups / maxSignup()) * 100)}%`" :title="`${day.day}: ${day.signups}`"></div>
                    </div>
                </template>
            </div>
        </article>
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Revenue</h2>
            <div class="mt-4 flex h-40 items-end gap-1 overflow-x-auto">
                <template x-for="day in series" :key="day.day + '-r'">
                    <div class="flex min-w-[10px] flex-1 flex-col items-center justify-end">
                        <div class="w-full rounded-t bg-gold-500" :style="`height:${Math.max(4, (Number(day.revenue_raw||0) / maxRevenue()) * 100)}%`" :title="`${day.day}: ${day.revenue}`"></div>
                    </div>
                </template>
            </div>
        </article>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <div>
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl">Recent payments</h2>
                <a href="{{ route('admin.payments.index') }}" class="font-semibold text-forest-800">View all</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($recentPayments as $payment)
                    <a href="{{ route('admin.payments.show', $payment) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                        <p class="font-semibold">{{ $payment->tx_ref }}</p>
                        <p class="mt-1 break-all text-sm text-stone-600">{{ $payment->user?->email }} · {{ $payment->amount_usd }} USD · {{ $payment->status->value }}</p>
                    </a>
                @empty
                    <p class="text-stone-600">No payments yet.</p>
                @endforelse
            </div>
        </div>
        <div>
            <h2 class="font-serif text-2xl">Pending items</h2>
            <ul class="mt-4 space-y-3">
                <li class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">Pending payments: <strong>{{ $pendingPayments }}</strong></li>
                <li class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">Open support tickets: <strong>{{ $pendingTickets }}</strong></li>
                <li class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">Pending referrals: <strong>{{ $openReferrals }}</strong></li>
            </ul>
        </div>
    </section>
@endsection