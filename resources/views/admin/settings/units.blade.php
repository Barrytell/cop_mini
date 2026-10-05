@extends('layouts.admin')
@section('title', 'Unit & settings')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Unit price & programme toggles</h1>
    <p class="mt-2 max-w-2xl text-stone-600">Unit price changes apply only to future payments. Existing snapshots stay unchanged.</p>
    <form method="POST" action="{{ route('admin.units.update') }}" class="mt-6 max-w-xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <div>
            <label class="label" for="unit_price_usd">Unit price (USD)</label>
            <input class="field" id="unit_price_usd" name="unit_price_usd" value="{{ old('unit_price_usd', $unitPrice) }}" required>
        </div>
        <div>
            <label class="label" for="min_payment_usd">Minimum payment (USD)</label>
            <input class="field" id="min_payment_usd" name="min_payment_usd" value="{{ old('min_payment_usd', $minPayment) }}" required>
        </div>
        <div>
            <label class="label" for="referral_bonus_units">Referral bonus units</label>
            <input class="field" id="referral_bonus_units" name="referral_bonus_units" type="number" min="0" max="1000" value="{{ old('referral_bonus_units', $referralBonus) }}" required>
        </div>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="registration_open" value="1" @checked(old('registration_open', $registrationOpen))> Registration open</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="referral_program_enabled" value="1" @checked(old('referral_program_enabled', $referralProgramEnabled))> Referral programme on</label>
        <label class="inline-flex min-h-11 items-center gap-2 font-semibold"><input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $maintenanceMode))> Maintenance mode</label>
        <button class="btn-primary" type="submit">Save</button>
    </form>
    <section class="mt-8">
        <h2 class="font-serif text-2xl">Unit price history</h2>
        <div class="mt-4 space-y-3">
            @forelse ($priceHistory as $change)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $change->old_value }} → {{ $change->new_value }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $change->actor?->email }} · {{ $change->created_at }} · {{ $change->note }}</p>
                </article>
            @empty
                <p class="text-stone-600">No price changes yet.</p>
            @endforelse
        </div>
    </section>
@endsection