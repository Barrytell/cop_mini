<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $siteName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-cream font-sans text-ink" x-data="{ navOpen: false }" :class="{ 'overflow-hidden': navOpen }" @keydown.escape.window="navOpen = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>

    <header class="border-b border-stone-200/80 bg-cream/95">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-3 px-4">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center font-serif text-xl font-semibold tracking-tight text-forest-900">{{ $siteName }}</a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="Primary">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Home</a>
                @foreach ($navPages as $navPage)
                    <a href="{{ route('pages.show', $navPage) }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">{{ $navPage->title }}</a>
                @endforeach
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary ml-2">Join</a>
            </nav>
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300 md:hidden" @click="navOpen = true" :aria-expanded="navOpen.toString()" aria-controls="public-drawer">
                <span class="sr-only">Open menu</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    <div id="public-drawer" x-cloak x-show="navOpen" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-ink/50" @click="navOpen = false"></div>
        <div class="absolute inset-y-0 right-0 flex w-[min(100%,20rem)] flex-col bg-cream shadow-xl">
            <div class="flex items-center justify-between px-4 py-3">
                <p class="font-serif text-lg font-semibold">Menu</p>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300" @click="navOpen = false">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1 px-3 pb-6" aria-label="Mobile">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Home</a>
                @foreach ($navPages as $navPage)
                    <a href="{{ route('pages.show', $navPage) }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">{{ $navPage->title }}</a>
                @endforeach
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary mt-2 w-full">Join</a>
            </nav>
        </div>
    </div>

    <main id="main">
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-forest-900 text-cream">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2">
            <div>
                <p class="font-serif text-2xl">{{ $siteName }}</p>
                <p class="mt-2 max-w-sm text-sm text-cream/80">A cooperative network. Members pool funds for real estate, gold, oil, and other stable assets.</p>
                <a href="mailto:{{ $contactEmail }}" class="mt-4 inline-flex min-h-11 items-center break-all text-sm font-semibold text-gold-400">{{ $contactEmail }}</a>
            </div>
            <div class="flex flex-wrap gap-3 sm:justify-end">
                @foreach ($socialLinks as $network => $url)
                    @if ($href = safe_url(is_string($url) ? $url : null))
                        <a href="{{ $href }}" class="inline-flex min-h-11 items-center rounded-full bg-white/10 px-4 text-sm font-semibold capitalize" rel="noopener noreferrer">{{ $network }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </footer>
</body>
</html>
