<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Member' }} · {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-cream font-sans text-ink" x-data="{ navOpen: false }" :class="{ 'overflow-hidden': navOpen }" @keydown.escape.window="navOpen = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>
    @php($member = auth()->user())
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-3 px-4">
            <a href="{{ $member->status->value === 'active' ? route('member.dashboard') : route('member.activate') }}" class="inline-flex min-h-11 min-w-0 items-center font-serif text-xl font-semibold text-forest-900">
                <span class="truncate">{{ $siteName }}</span>
            </a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="Member">
                @if ($member->status->value === 'active')
                    <a href="{{ route('member.dashboard') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">Dashboard</a>
                @endif
                <a href="{{ $member->status->value === 'pending' ? route('member.activate') : route('member.units.buy') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">{{ $member->status->value === 'pending' ? 'Activate' : 'Buy units' }}</a>
                <a href="{{ route('member.payments.index') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">Payments</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">Log out</button>
                </form>
            </nav>
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-cream ring-1 ring-stone-300 md:hidden" @click="navOpen = true" :aria-expanded="navOpen.toString()" aria-controls="member-drawer">
                <span class="sr-only">Open menu</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    <div id="member-drawer" x-cloak x-show="navOpen" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Member menu">
        <div class="absolute inset-0 bg-ink/50" @click="navOpen = false"></div>
        <div class="absolute inset-y-0 right-0 flex w-[min(100%,20rem)] flex-col bg-cream shadow-xl">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate font-semibold">{{ $member->name }}</p>
                    <p class="text-sm text-stone-600">{{ $member->member_no }}</p>
                </div>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300" @click="navOpen = false">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1 px-3 pb-6">
                @if ($member->status->value === 'active')
                    <a href="{{ route('member.dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Dashboard</a>
                @endif
                <a href="{{ $member->status->value === 'pending' ? route('member.activate') : route('member.units.buy') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">{{ $member->status->value === 'pending' ? 'Activate' : 'Buy units' }}</a>
                <a href="{{ route('member.payments.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Payments</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-11 w-full items-center rounded-xl px-3 text-left font-semibold">Log out</button>
                </form>
            </nav>
        </div>
    </div>

    <main id="main" class="mx-auto w-full max-w-6xl px-4 py-8">
        @include('layouts.partials.flash')
        @yield('content')
    </main>
</body>
</html>
