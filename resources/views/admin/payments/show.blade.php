@extends('layouts.admin')
@section('title', $payment->tx_ref)
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $payment->tx_ref }}</h1>
    <p class="mt-2 text-stone-600">{{ $payment->user?->name }} · {{ $payment->amount_usd }} USD · {{ $payment->units_purchased }} units · {{ $payment->status->value }}</p>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Actions</h2>
            <form method="POST" action="{{ route('admin.payments.reverify', $payment) }}" class="mt-3">@csrf<button class="btn-primary" type="submit">Re-verify with Flutterwave</button></form>
            @if(auth()->user()?->role->value === 'super_admin' && $payment->status->value !== 'successful')
                @include('admin.partials.reason-form', ['action' => route('admin.payments.mark-successful', $payment), 'label' => 'Mark successful'])
            @endif
            @if($payment->status->value === 'successful')
                @include('admin.partials.reason-form', ['action' => route('admin.payments.refund', $payment), 'label' => 'Log refund'])
            @endif
        </section>
        <section class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            <h2 class="font-serif text-2xl">Refunds log</h2>
            <div class="mt-3 space-y-2">
                @forelse ($refunds as $refund)
                    <p class="text-sm">{{ $refund->amount_usd }} USD · {{ $refund->reason }} · {{ $refund->created_at }}</p>
                @empty
                    <p class="text-stone-600">No refunds logged.</p>
                @endforelse
            </div>
        </section>
    </div>
    <section class="mt-8 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        <h2 class="font-serif text-2xl">Gateway payload</h2>
        <pre class="mt-3 max-h-96 overflow-auto rounded-2xl bg-stone-950 p-4 text-xs text-cream">{{ json_encode($payment->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </section>
@endsection