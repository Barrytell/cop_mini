@extends('layouts.admin')
@section('title', 'System health')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">System health</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200"><p class="text-sm text-stone-600">Queue</p><p class="mt-2 font-serif text-2xl">{{ $queueConnection }}</p></article>
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200"><p class="text-sm text-stone-600">Pending jobs</p><p class="mt-2 font-serif text-2xl">{{ $pendingJobs }}</p></article>
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200"><p class="text-sm text-stone-600">Last reconcile</p><p class="mt-2 font-serif text-xl">{{ $lastReconcile ?: 'never' }}</p></article>
        <article class="rounded-3xl bg-white p-5 ring-1 ring-stone-200"><p class="text-sm text-stone-600">Maintenance</p><p class="mt-2 font-serif text-2xl">{{ $maintenance ? 'on' : 'off' }}</p></article>
    </div>
    <div class="mt-6 flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.system.backup') }}">@csrf<button class="btn-primary" type="submit">Trigger backup</button></form>
        <form method="POST" action="{{ route('admin.system.retry-failed') }}">@csrf<button class="btn-secondary" type="submit">Retry failed jobs</button></form>
        <a class="btn-secondary" href="{{ route('admin.audit.index') }}">Audit log</a>
    </div>
    <section class="mt-8">
        <h2 class="font-serif text-2xl">Failed jobs</h2>
        <div class="mt-4 space-y-3">
            @forelse ($failedJobs as $job)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200"><p class="font-semibold">#{{ $job->id }} · {{ $job->queue }}</p><p class="mt-1 break-all text-xs text-stone-600">{{ \Illuminate\Support\Str::limit($job->exception, 240) }}</p></article>
            @empty
                <p class="text-stone-600">No failed jobs.</p>
            @endforelse
        </div>
    </section>
    <section class="mt-8">
        <h2 class="font-serif text-2xl">Webhook log</h2>
        <div class="mt-4 space-y-3">
            @forelse ($webhooks as $event)
                <article class="rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                    <p class="font-semibold">{{ $event->event ?: 'webhook' }} · {{ $event->tx_ref }}</p>
                    <p class="mt-1 text-sm text-stone-600">HTTP {{ $event->http_status }} · sig {{ $event->signature_valid ? 'valid' : 'invalid' }} · {{ $event->created_at }}</p>
                </article>
            @empty
                <p class="text-stone-600">No webhook events.</p>
            @endforelse
        </div>
    </section>
@endsection