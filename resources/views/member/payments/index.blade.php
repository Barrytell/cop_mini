@extends('layouts.member')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Payments</h1>
            <p class="mt-2 max-w-2xl text-stone-700">Pending checkouts stay here until Flutterwave confirms them. A confirmed payment can be downloaded as a receipt.</p>
        </div>
        <a href="{{ auth()->user()->status->value === 'active' ? route('member.units.buy') : route('member.activate') }}" class="btn-primary">{{ auth()->user()->status->value === 'active' ? 'Buy units' : 'Activate' }}</a>
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($payments as $payment)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="break-all font-semibold">{{ $payment->tx_ref }}</p>
                        <p class="mt-1 text-sm text-stone-700">
                            ${{ $payment->amount_usd }} USD
                            @if ($payment->charge_currency && $payment->charge_currency !== 'USD')
                                · charged {{ $payment->charge_amount }} {{ $payment->charge_currency }}
                            @endif
                            · {{ number_format($payment->units_purchased) }} units
                            · {{ str_replace('_', ' ', $payment->type->value) }}
                            · {{ $payment->status->value }}
                        </p>
                        <p class="mt-1 text-xs text-stone-500">{{ $payment->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                    </div>
                    @if ($payment->status->value === 'successful')
                        <a href="{{ route('member.payments.receipt', $payment) }}" class="btn-ghost shrink-0">Download receipt</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="text-stone-600">No payments yet.</p>
        @endforelse
    </div>

    <div class="mt-6 overflow-x-auto">{{ $payments->links() }}</div>
@endsection
