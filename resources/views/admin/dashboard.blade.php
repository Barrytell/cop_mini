@extends('layouts.admin')

@section('content')
    <h1 class="font-serif text-3xl font-semibold sm:text-4xl">Overview</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Members' => $memberCount,
            'Pending' => $pendingCount,
            'Active members' => $activeCount,
            'Successful payments' => $successfulPayments,
        ] as $label => $value)
            <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200">
                <p class="text-sm font-semibold text-stone-600">{{ $label }}</p>
                <p class="mt-2 font-serif text-3xl">{{ number_format($value) }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-8">
        <h2 class="font-serif text-2xl">Recent members</h2>
        <div class="mt-4 space-y-3">
            @forelse ($recentMembers as $member)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $member->name }}</p>
                    <p class="mt-1 break-all text-sm text-stone-600">{{ $member->member_no }} · {{ $member->email }} · {{ $member->status->value }}</p>
                </article>
            @empty
                <p class="text-stone-600">No members yet.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-serif text-2xl">Recent admin actions</h2>
            <a href="{{ route('admin.audit.index') }}" class="inline-flex min-h-11 items-center font-semibold text-forest-800">View all</a>
        </div>
        <div class="mt-4 space-y-3">
            @forelse ($recentAudits as $log)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $log->action }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $log->user?->email ?? 'System' }} · {{ $log->ip_address }} · {{ $log->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                </article>
            @empty
                <p class="text-stone-600">No audit entries yet.</p>
            @endforelse
        </div>
    </section>
@endsection
