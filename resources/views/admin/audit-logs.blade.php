@extends('layouts.admin')

@section('content')
    <h1 class="font-serif text-3xl font-semibold sm:text-4xl">Audit log</h1>
    <div class="mt-6 space-y-3">
        @forelse ($logs as $log)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <p class="font-semibold">{{ $log->action }}</p>
                    <p class="text-sm text-stone-500">{{ $log->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                </div>
                <p class="mt-1 break-all text-sm text-stone-600">{{ $log->user?->name ?? 'System' }} · {{ $log->user?->email }} · {{ $log->ip_address }}</p>
                @if ($log->old_values)
                    <pre class="mt-3 max-w-full overflow-x-auto whitespace-pre-wrap break-words rounded-xl bg-stone-50 p-3 text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                @endif
                @if ($log->new_values)
                    <pre class="mt-2 max-w-full overflow-x-auto whitespace-pre-wrap break-words rounded-xl bg-forest-50 p-3 text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                @endif
            </article>
        @empty
            <p class="text-stone-600">No audit entries yet.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
@endsection
