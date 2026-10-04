<form method="POST" action="{{ route('member.payments.store') }}" class="space-y-4">
    @csrf
    <div>
        <label class="label" for="amount_usd">Amount (USD)</label>
        <input class="field" id="amount_usd" name="amount_usd" type="text" inputmode="decimal" value="{{ old('amount_usd', $minimum) }}" required autocomplete="off">
        <p class="mt-1 text-sm text-stone-600">Minimum ${{ $minimum }}. Unit price right now is ${{ $unitPrice }}. That price is stored on the payment.</p>
    </div>
    <button type="submit" class="btn-primary w-full sm:w-auto">Continue to Flutterwave</button>
</form>
