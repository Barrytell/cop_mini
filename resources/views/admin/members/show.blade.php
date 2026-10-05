@extends('layouts.admin')
@section('title', $member->name)
@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-semibold">{{ $member->name }}</h1>
            <p class="mt-2 break-all text-stone-600">{{ $member->member_no }} · {{ $member->email }} · {{ $member->status->value }} · {{ $member->country }}</p>
            <p class="mt-1 text-stone-600">Balance: <strong>{{ number_format($balance) }}</strong> units · Referrer: {{ $member->referrer?->email ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn-secondary" href="{{ route('admin.members.edit', $member) }}">Edit</a>
            @can('impersonate', $member)
                <form method="POST" action="{{ route('admin.members.impersonate', $member) }}">@csrf<button class="btn-gold" type="submit">Impersonate</button></form>
            @endcan
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Actions</h2>
            @if($member->trashed())
                @include('admin.partials.reason-form', ['action' => route('admin.members.restore', $member), 'label' => 'Restore'])
            @else
                @if($member->status->value === 'pending')
                    @include('admin.partials.reason-form', ['action' => route('admin.members.activate', $member), 'label' => 'Activate manually'])
                @endif
                @if($member->status->value === 'suspended')
                    @include('admin.partials.reason-form', ['action' => route('admin.members.unsuspend', $member), 'label' => 'Unsuspend'])
                @else
                    @include('admin.partials.reason-form', ['action' => route('admin.members.suspend', $member), 'label' => 'Suspend'])
                @endif
                @include('admin.partials.reason-form', ['action' => route('admin.members.reset-password', $member), 'label' => 'Reset password'])
                @include('admin.partials.reason-form', ['action' => route('admin.members.destroy', $member), 'method' => 'DELETE', 'label' => 'Soft delete', 'confirm' => 'Soft-delete this member?'])
            @endif
        </section>
        <section class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Adjust units</h2>
            <form method="POST" action="{{ route('admin.members.units', $member) }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="label" for="units">Units (negative to deduct)</label>
                    <input class="field" id="units" name="units" type="number" required>
                </div>
                <div>
                    <label class="label" for="note">Note</label>
                    <textarea class="field min-h-24" id="note" name="note" required maxlength="500"></textarea>
                </div>
                <button class="btn-primary" type="submit">Post adjustment</button>
            </form>
        </section>
    </div>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Payments</h2>
        <div class="mt-4 space-y-3">
            @forelse ($payments as $payment)
                <a href="{{ route('admin.payments.show', $payment) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">{{ $payment->tx_ref }} · {{ $payment->amount_usd }} · {{ $payment->status->value }}</a>
            @empty
                <p class="text-stone-600">No payments.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </section>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Units ledger</h2>
        <div class="mt-4 space-y-3">
            @forelse ($ledger as $entry)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $entry->units > 0 ? '+' : '' }}{{ $entry->units }} · {{ $entry->type->value }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $entry->note }} · {{ $entry->created_at }}</p>
                </article>
            @empty
                <p class="text-stone-600">No ledger rows.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $ledger->links() }}</div>
    </section>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Referrals made</h2>
        <div class="mt-4 space-y-3">
            @forelse ($referrals as $referral)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $referral->referred?->name }} · {{ $referral->status->value }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $referral->referred?->email }} · bonus {{ $referral->bonus_units }}</p>
                </article>
            @empty
                <p class="text-stone-600">No referrals.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $referrals->links() }}</div>
    </section>
@endsection