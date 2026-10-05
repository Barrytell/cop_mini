<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Member') · {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-cream font-sans text-ink" x-data="{ navOpen: false }" :class="{ 'overflow-hidden': navOpen }" @keydown.escape.window="navOpen = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>
    @php
        $member = auth()->user();
        $active = $member->status->value === 'active';
        $links = array_filter([
            $active ? ['Dashboard', route('member.dashboard')] : ['Activate', route('member.activate')],
            $active ? ['Buy units', route('member.units.buy')] : null,
            ['Payments', route('member.payments.index')],
            ['Referrals', route('member.referrals.index')],
            ['Statement', route('member.ledger.index')],
            ['News', route('member.announcements.index'), $unreadAnnouncementCount ?? 0],
            ['Meetings', route('member.meetings.index')],
            ['Support', route('member.support.index')],
            ['Profile', route('member.profile.edit')],
        ]);
    @endphp
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-3 px-4">
            <a href="{{ $active ? route('member.dashboard') : route('member.activate') }}" class="inline-flex min-h-11 min-w-0 items-center font-serif text-xl font-semibold text-forest-900">
                <span class="truncate">{{ $siteName }}</span>
            </a>
            <div class="flex items-center gap-1">
                <a href="{{ route('member.notifications.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-full hover:bg-cream" aria-label="Notifications{{ ($unreadNotificationCount ?? 0) > 0 ? ', '.($unreadNotificationCount).' unread' : '' }}">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 3a4 4 0 00-4 4v2.2L4.3 12.2A1 1 0 005.1 14h9.8a1 1 0 00.8-1.8L14 9.2V7a4 4 0 00-4-4zM8 15a2 2 0 004 0" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                    @if (($unreadNotificationCount ?? 0) > 0)
                        <span class="absolute right-1 top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-gold-500 px-1 text-[11px] font-semibold">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                    @endif
                </a>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-cream ring-1 ring-stone-300 lg:hidden" @click="navOpen = true" :aria-expanded="navOpen.toString()" aria-controls="member-drawer">
                    <span class="sr-only">Open menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        <nav class="mx-auto hidden max-w-6xl gap-1 overflow-x-auto px-4 pb-3 lg:flex" aria-label="Member">
            @foreach ($links as $link)
                <a href="{{ $link[1] }}" class="inline-flex min-h-11 shrink-0 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">
                    {{ $link[0] }}
                    @if (($link[2] ?? 0) > 0)
                        <span class="ml-2 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-gold-500 px-1 text-[11px]">{{ $link[2] }}</span>
                    @endif
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-cream">Log out</button>
            </form>
        </nav>
    </header>

    <div id="member-drawer" x-cloak x-show="navOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Member menu">
        <div class="absolute inset-0 bg-ink/50" @click="navOpen = false"></div>
        <div class="absolute inset-y-0 right-0 flex w-[min(100%,20rem)] flex-col overflow-y-auto bg-cream shadow-xl">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate font-semibold">{{ $member->name }}</p>
                    <p class="break-all text-sm text-stone-600">{{ str_starts_with((string) $member->member_no, 'TMP-') ? 'Pending member' : $member->member_no }}</p>
                </div>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300" @click="navOpen = false">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1 px-3 pb-6">
                @foreach ($links as $link)
                    <a href="{{ $link[1] }}" class="inline-flex min-h-11 items-center justify-between rounded-xl px-3 font-semibold">
                        <span>{{ $link[0] }}</span>
                        @if (($link[2] ?? 0) > 0)
                            <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full bg-gold-500 px-1 text-xs">{{ $link[2] }}</span>
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('member.notifications.index') }}" class="inline-flex min-h-11 items-center justify-between rounded-xl px-3 font-semibold">
                    <span>Notifications</span>
                    @if (($unreadNotificationCount ?? 0) > 0)
                        <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full bg-gold-500 px-1 text-xs">{{ $unreadNotificationCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-11 w-full items-center rounded-xl px-3 text-left font-semibold">Log out</button>
                </form>
            </nav>
        </div>
    </div>

    @yield('banner')

    <main id="main" class="mx-auto w-full max-w-6xl px-4 py-8">
        @include('layouts.partials.flash')
        @yield('content')
    </main>
</body>
</html>
