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
<body class="min-h-screen overflow-x-hidden bg-stone-100 font-sans text-ink" x-data="{ navOpen: false }" :class="{ 'overflow-hidden': navOpen }" @keydown.escape.window="navOpen = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>
    <div class="md:grid md:grid-cols-[16rem_minmax(0,1fr)] md:min-h-screen">
        <aside class="hidden bg-forest-900 text-cream md:flex md:flex-col">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-16 items-center px-5 font-serif text-xl font-semibold">{{ $siteName }}</a>
            <nav class="flex flex-1 flex-col gap-1 px-3" aria-label="Admin">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold hover:bg-white/10">Overview</a>
                <a href="{{ route('admin.settings.edit') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold hover:bg-white/10">Settings</a>
                <a href="{{ route('admin.audit.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold hover:bg-white/10">Audit log</a>
                <a href="{{ route('admin.support.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold hover:bg-white/10">Support</a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="p-3">
                @csrf
                <button type="submit" class="inline-flex min-h-11 w-full items-center rounded-xl px-3 text-left font-semibold hover:bg-white/10">Log out</button>
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
                        <p class="font-serif text-lg">Admin</p>
                        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10" @click="navOpen = false">
                            <span class="sr-only">Close menu</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <nav class="flex flex-col gap-1 px-3">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Overview</a>
                        <a href="{{ route('admin.settings.edit') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Settings</a>
                        <a href="{{ route('admin.audit.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Audit log</a>
                        <a href="{{ route('admin.support.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Support</a>
                    </nav>
                    <form method="POST" action="{{ route('logout') }}" class="mt-auto p-3">
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
