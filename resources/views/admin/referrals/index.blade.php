@extends('layouts.admin')
@section('title', 'Referrals')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Referrals</h1>
    <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status">
                <option value="">All</option>
                @foreach (['pending','rewarded'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary" type="submit">Filter</button>
    </form>
    <section class="mt-8">
        <h2 class="font-serif text-2xl">Leaderboard</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($leaderboard as $row)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $row->referrer?->name ?? 'Unknown' }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $row->rewarded_count }} rewarded / {{ $row->total }} total · {{ (int) $row->bonus_units }} units</p>
                </article>
            @endforeach
        </div>
    </section>
    <div class="mt-8 space-y-4">
        @forelse ($referrals as $referral)
            <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $referral->referrer?->email }} → {{ $referral->referred?->email }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $referral->status->value }} · bonus {{ $referral->bonus_units }}</p>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    @include('admin.partials.reason-form', ['action' => route('admin.referrals.reward', $referral), 'label' => 'Manual reward'])
                    @if($referral->status->value === 'rewarded')
                        @include('admin.partials.reason-form', ['action' => route('admin.referrals.reverse', $referral), 'label' => 'Reverse reward'])
                    @endif
                </div>
            </article>
        @empty
            <p class="text-stone-600">No referrals.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $referrals->links() }}</div>
@endsection