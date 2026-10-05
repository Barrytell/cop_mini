@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-semibold">Payments</h1>
            <p class="mt-2 text-stone-600">All-time successful revenue: <strong>{{ $revenue }}</strong></p>
        </div>
        <a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a>
    </div>
    <form method="GET" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-stone-200 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status">
                <option value="">All</option>
                @foreach (['pending','successful','failed','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="type">Type</label>
            <input class="field" id="type" name="type" value="{{ $filters['type'] ?? '' }}" placeholder="activation / topup">
        </div>
        <div>
            <label class="label" for="from">From</label>
            <input class="field" type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div>
            <label class="label" for="to">To</label>
            <input class="field" type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div>
            <label class="label" for="min">Min amount</label>
            <input class="field" id="min" name="min" value="{{ $filters['min'] ?? '' }}">
        </div>
        <div>
            <label class="label" for="max">Max amount</label>
            <input class="field" id="max" name="max" value="{{ $filters['max'] ?? '' }}">
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
            <button class="btn-primary" type="submit">Filter</button>
            <a class="btn-secondary" href="{{ route('admin.payments.index') }}">Reset</a>
        </div>
    </form>
    <div class="mt-6 overflow-x-auto rounded-3xl bg-white ring-1 ring-stone-200">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-stone-600">
                <tr>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Member</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-b border-stone-100">
                        <td class="px-4 py-3"><a class="font-semibold text-forest-800" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->tx_ref }}</a></td>
                        <td class="px-4 py-3 break-all">{{ $payment->user?->email }}</td>
                        <td class="px-4 py-3">{{ $payment->amount_usd }} USD</td>
                        <td class="px-4 py-3">{{ $payment->status->value }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-stone-600">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $payments->links() }}</div>
@endsection