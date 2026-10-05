<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $admin = auth()->user();
    $nav = array_values(array_filter([
        $admin?->canAccess('dashboard') ? ['Dashboard', route('admin.dashboard'), 'dashboard'] : null,
        $admin?->canAccess('members') ? ['Members', route('admin.members.index'), 'members.*'] : null,
        $admin?->canAccess('payments') ? ['Payments', route('admin.payments.index'), 'payments.*'] : null,
        $admin?->canAccess('settings') ? ['Unit & settings', route('admin.units.edit'), 'units.*'] : null,
        $admin?->canAccess('settings') ? ['Site settings', route('admin.settings.edit'), 'settings.*'] : null,
        $admin?->canAccess('referrals') ? ['Referrals', route('admin.referrals.index'), 'referrals.*'] : null,
        $admin?->canAccess('communications') ? ['Announcements', route('admin.announcements.index'), 'announcements.*'] : null,
        $admin?->canAccess('communications') ? ['Meetings', route('admin.meetings.index'), 'meetings.*'] : null,
        $admin?->canAccess('communications') ? ['Outbound', route('admin.outbound.index'), 'outbound.*'] : null,
        $admin?->canAccess('communications') ? ['Email templates', route('admin.email-templates.index'), 'email-templates.*'] : null,
        $admin?->canAccess('content') ? ['Pages', route('admin.cms.pages.index'), 'cms.pages.*'] : null,
        $admin?->canAccess('content') ? ['CMS', route('admin.cms.content.index', 'posts'), 'cms.content.*'] : null,
        $admin?->canAccess('banners') ? ['Banners', route('admin.banners.index'), 'banners.*'] : null,
        $admin?->canAccess('support') ? ['Contact', route('admin.contact.index'), 'contact.*'] : null,
        $admin?->canAccess('support') ? ['Support', route('admin.support.index'), 'support.*'] : null,
        $admin?->canAccess('admins') ? ['Admins', route('admin.admins.index'), 'admins.*'] : null,
        $admin?->canAccess('reports') ? ['Reports', route('admin.reports.index'), 'reports.*'] : null,
        $admin?->canAccess('system') ? ['Audit log', route('admin.audit.index'), 'audit.*'] : null,
        $admin?->canAccess('system') ? ['System', route('admin.system.health'), 'system.*'] : null,
        ['2FA', route('admin.totp.setup'), 'totp.*'],
    ]));
@endphp
<body class="min-h-screen overflow-x-hidden bg-stone-100 font-sans text-ink"
      x-data="{ navOpen: false, collapsed: localStorage.getItem('adminSidebar') === '1' }"
      x-init="$watch('collapsed', v => localStorage.setItem('adminSidebar', v ? '1' : '0'))"
      :class="{ 'overflow-hidden': navOpen }"
      @keydown.escape.window="navOpen = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>
    <div class="md:grid md:min-h-screen" :class="collapsed ? 'md:grid-cols-[4.5rem_minmax(0,1fr)]' : 'md:grid-cols-[16rem_minmax(0,1fr)]'">
        <aside class="hidden bg-forest-900 text-cream md:flex md:flex-col">
            <div class="flex min-h-16 items-center justify-between gap-2 px-3">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 min-w-0 items-center font-serif text-xl font-semibold" :class="collapsed && 'justify-center px-0'">
                    <span x-show="!collapsed" x-cloak class="truncate">{{ $siteName }}</span>
                    <span x-show="collapsed" x-cloak aria-hidden="true">M</span>
                </a>
                <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl hover:bg-white/10" @click="collapsed = !collapsed" :aria-expanded="(!collapsed).toString()" aria-controls="admin-side-nav">
                    <span class="sr-only">Toggle sidebar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4l-4 6 4 6M13 4l4 6-4 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <nav id="admin-side-nav" class="flex flex-1 flex-col gap-1 overflow-y-auto px-2 pb-4" aria-label="Admin">
                @foreach ($nav as [$label, $url, $pattern])
                    <a href="{{ $url }}"
                       class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold hover:bg-white/10 {{ request()->routeIs($pattern) ? 'bg-white/15 text-gold-400' : '' }}"
                       :class="collapsed && 'justify-center px-0'"
                       title="{{ $label }}">
                        <span x-show="!collapsed" x-cloak>{{ $label }}</span>
                        <span x-show="collapsed" x-cloak class="text-sm">{{ strtoupper(substr($label, 0, 1)) }}</span>
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="p-2">
                @csrf
                <button type="submit" class="inline-flex min-h-11 w-full items-center rounded-xl px-3 text-left font-semibold hover:bg-white/10" :class="collapsed && 'justify-center px-0'">
                    <span x-show="!collapsed" x-cloak>Log out</span>
                    <span x-show="collapsed" x-cloak>↪</span>
                </button>
            </form>
        </aside>

        <div class="min-w-0">
            <header class="flex min-h-16 items-center justify-between border-b border-stone-200 bg-white px-4 md:hidden">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 items-center font-serif text-xl font-semibold">Admin</a>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-stone-100" @click="navOpen = true" :aria-expanded="navOpen.toString()" aria-controls="admin-drawer">
                    <span class="sr-only">Open menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </header>

            <div id="admin-drawer" x-cloak x-show="navOpen" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Admin menu">
                <div class="absolute inset-0 bg-ink/50" @click="navOpen = false"></div>
                <div class="absolute inset-y-0 left-0 flex w-[min(100%,20rem)] flex-col bg-forest-900 text-cream shadow-xl">
                    <div class="flex items-center justify-between px-4 py-3">
                        <p class="font-serif text-lg">{{ $siteName }}</p>
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10" @click="navOpen = false">
                            <span class="sr-only">Close menu</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 pb-4">
                        @foreach ($nav as [$label, $url, $pattern])
                            <a href="{{ $url }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold {{ request()->routeIs($pattern) ? 'bg-white/15 text-gold-400' : '' }}" @click="navOpen = false">{{ $label }}</a>
                        @endforeach
                    </nav>
                    <form method="POST" action="{{ route('logout') }}" class="p-3">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center rounded-xl px-3 text-left font-semibold">Log out</button>
                    </form>
                </div>
            </div>

            <main id="main" class="mx-auto w-full max-w-6xl px-4 py-8">
                @include('layouts.partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
