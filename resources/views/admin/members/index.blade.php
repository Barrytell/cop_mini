@extends('layouts.admin')
@section('title', 'Members')
@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-semibold">Members</h1>
            <p class="mt-2 text-stone-600">Search, filter, and manage membership accounts.</p>
        </div>
        <a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a>
    </div>
    <form method="GET" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-stone-200 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="label" for="q">Search</label>
            <input class="field" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, member no">
        </div>
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status">
                <option value="">All</option>
                @foreach (['pending','active','suspended'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="country">Country</label>
            <select class="field" id="country" name="country">
                <option value="">All</option>
                @foreach ($countries as $country)
                    <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="from">From</label>
            <input class="field" type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div>
            <label class="label" for="to">To</label>
            <input class="field" type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
            <button class="btn-primary" type="submit">Filter</button>
            <a class="btn-secondary" href="{{ route('admin.members.index') }}">Reset</a>
        </div>
    </form>
    <div class="mt-6 overflow-x-auto rounded-3xl bg-white ring-1 ring-stone-200">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-stone-600">
                <tr>
                    <th class="px-4 py-3">Member</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Country</th>
                    <th class="px-4 py-3">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr class="border-b border-stone-100">
                        <td class="px-4 py-3">
                            <a class="font-semibold text-forest-800" href="{{ route('admin.members.show', $member) }}">{{ $member->name }}</a>
                            <p class="break-all text-stone-600">{{ $member->member_no }} · {{ $member->email }}</p>
                            @if($member->trashed())
                                <p class="text-xs font-semibold text-red-700">Soft-deleted</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $member->status->value }}</td>
                        <td class="px-4 py-3">{{ $member->country }}</td>
                        <td class="px-4 py-3">{{ $member->created_at?->toDateString() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-stone-600">No members match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $members->links() }}</div>
@endsection