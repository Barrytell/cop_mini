@extends('layouts.admin')

@section('content')
    <h1 class="font-serif text-3xl font-semibold sm:text-4xl">Settings</h1>
    <p class="mt-2 max-w-2xl text-stone-700">These values apply to new payments and new referral rewards. Existing ledger rows stay as they were written.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <div>
            <label class="label" for="site_name">Site name</label>
            <input class="field" id="site_name" name="site_name" type="text" value="{{ old('site_name', $siteName) }}" required maxlength="80">
        </div>
        <div>
            <label class="label" for="contact_email">Contact email</label>
            <input class="field" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $contactEmail) }}" required maxlength="255">
        </div>
        <div>
            <label class="label" for="whatsapp_number">WhatsApp number</label>
            <input class="field" id="whatsapp_number" name="whatsapp_number" type="text" value="{{ old('whatsapp_number', $whatsappNumber) }}" maxlength="24" placeholder="+2348000000000">
        </div>
        <div>
            <label class="label" for="office_address">Office address</label>
            <textarea class="field min-h-28" id="office_address" name="office_address" maxlength="500">{{ old('office_address', $officeAddress) }}</textarea>
        </div>
        <div>
            <label class="label" for="map_embed_url">Map embed URL</label>
            <input class="field" id="map_embed_url" name="map_embed_url" type="url" value="{{ old('map_embed_url', $mapEmbedUrl) }}" maxlength="500" placeholder="https://www.openstreetmap.org/export/embed.html">
        </div>
        <div>
            <label class="label" for="unit_price_usd">Unit price (USD)</label>
            <input class="field" id="unit_price_usd" name="unit_price_usd" type="text" inputmode="decimal" value="{{ old('unit_price_usd', $unitPrice) }}" required>
        </div>
        <div>
            <label class="label" for="min_payment_usd">Minimum payment (USD)</label>
            <input class="field" id="min_payment_usd" name="min_payment_usd" type="text" inputmode="decimal" value="{{ old('min_payment_usd', $minimum) }}" required>
        </div>
        <div>
            <label class="label" for="referral_bonus_units">Referral bonus (units)</label>
            <input class="field" id="referral_bonus_units" name="referral_bonus_units" type="number" min="0" max="1000" value="{{ old('referral_bonus_units', $referralBonus) }}" required>
        </div>
        @foreach (['facebook' => 'Facebook', 'x' => 'X', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube'] as $key => $label)
            <div>
                <label class="label" for="social_{{ $key }}">{{ $label }} URL</label>
                <input class="field" id="social_{{ $key }}" name="social_{{ $key }}" type="url" value="{{ old('social_'.$key, $social[$key] ?? '') }}" maxlength="255" placeholder="https://">
            </div>
        @endforeach
        <button type="submit" class="btn-primary">Save settings</button>
    </form>
@endsection
