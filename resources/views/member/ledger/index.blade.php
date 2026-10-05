@extends('layouts.member')

@section('title', 'Units statement')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Units statement</h1>
        <a href="{{ route('member.ledger.export', request()->only(['type', 'from', 'to'])) }}" class="btn-ghost">Download CSV</a>
    </div>

    <form method="GET" action="{{ route('member.ledger.index') }}" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="label" for="type">Type</label>
            <select class="field" id="type" name="type">
                <option value="">All</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ str_replace('_', ' ', $type->value) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="from">From</label>
            <input class="field" id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div>
            <label class="label" for="to">To</label>
            <input class="field" id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">Filter</button>
        </div>
    </form>

    <div class="mt-6 space-y-3">
        @forelse ($entries as $entry)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ str_replace('_', ' ', $entry->type->value) }}</p>
                        <p class="mt-1 break-words text-sm text-stone-600">{{ $entry->note }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ $entry->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                    </div>
                    <p class="font-semibold {{ $entry->units < 0 ? 'text-red-700' : 'text-forest-800' }}">{{ $entry->units > 0 ? '+' : '' }}{{ number_format($entry->units) }}</p>
                </div>
            </article>
        @empty
            <p class="text-stone-600">No ledger rows match this filter.</p>
        @endforelse
    </div>
    <div class="mt-6 overflow-x-auto">{{ $entries->links() }}</div>
@endsection
