@extends('layouts.member')

@section('title', 'Notifications')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Notifications</h1>
        @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('member.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn-ghost">Mark all read</button>
            </form>
        @endif
    </div>
    <div class="mt-6 space-y-3">
        @forelse ($notifications as $notification)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200 {{ $notification->read_at ? '' : 'ring-gold-400' }}">
                <p class="font-semibold">
                    @if (isset($notification->data['tx_ref']))
                        Payment {{ $notification->data['tx_ref'] }}
                    @elseif (isset($notification->data['bonus_units']))
                        Referral bonus of {{ number_format((int) $notification->data['bonus_units']) }} units
                    @elseif (isset($notification->data['subject']))
                        Support: {{ $notification->data['subject'] }}
                    @else
                        Update
                    @endif
                </p>
                <p class="mt-1 text-sm text-stone-600">{{ $notification->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                @if ($notification->read_at === null)
                    <form method="POST" action="{{ route('member.notifications.read', $notification) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-ghost">Open</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="text-stone-600">No notifications yet.</p>
        @endforelse
    </div>
    <div class="mt-6 overflow-x-auto">{{ $notifications->links() }}</div>
@endsection
