@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-semibold">Reports</h1>
            <p class="mt-2 text-stone-600">Financial summary, membership growth, units, and referrals.</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div><label class="label" for="from">From</label><input class="field" type="date" id="from" name="from" value="{{ $from }}"></div>
            <div><label class="label" for="to">To</label><input class="field" type="date" id="to" name="to" value="{{ $to }}"></div>
            <button class="btn-primary" type="submit">Apply</button>
            <a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">CSV</a>
            <a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">PDF</a>
        </form>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($summary as $key => $value)
            <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                <p class="text-sm font-semibold text-stone-600">{{ str_replace('_', ' ', $key) }}</p>
                <p class="mt-2 font-serif text-3xl">{{ $value }}</p>
            </article>
        @endforeach
    </div>
@endsection