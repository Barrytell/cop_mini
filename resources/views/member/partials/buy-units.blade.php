<form
    method="POST"
    action="{{ route('member.payments.store') }}"
    class="space-y-4"
    x-data="unitQuote"
    data-unit-price="{{ $unitPrice }}"
    data-price-label="{{ rtrim(rtrim($unitPrice, '0'), '.') }}"
    data-minimum="{{ old('amount_usd', $minimum) }}"
    data-currency="{{ old('currency', 'USD') }}"
    data-quote-url="{{ route('member.payments.quote') }}"
    @submit="sending = true"
>
    @csrf
    <div>
        <label class="label" for="amount_usd">Amount (USD)</label>
        <input class="field" id="amount_usd" name="amount_usd" type="text" inputmode="decimal" x-model="amount" @input="calculate" value="{{ old('amount_usd', $minimum) }}" required autocomplete="off">
        <p class="mt-1 text-sm text-stone-600">Minimum ${{ $minimum }}. Units are calculated from this USD amount.</p>
    </div>
    <div>
        <label class="label" for="currency">Currency to pay</label>
        <select class="field" id="currency" name="currency" x-model="currency" @change="calculate">
            @foreach ($currencies as $code => $meta)
                <option value="{{ $code }}" @selected(old('currency', 'USD') === $code)>{{ $code }} — {{ $meta['label'] }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-sm text-stone-600">Card, bank transfer, USSD, and mobile money are offered where that currency supports them. Flutterwave converts a non-USD choice.</p>
    </div>
    <p class="rounded-2xl bg-cream px-4 py-3 text-sm text-forest-900" aria-live="polite">
        You will receive <span class="font-semibold" x-text="unitsLabel"></span> units at $<span x-text="priceLabel"></span>/unit.
        <span class="mt-1 block" x-show="chargeLabel" x-text="chargeLabel"></span>
    </p>
    <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="sending">Pay with Flutterwave</button>
</form>
