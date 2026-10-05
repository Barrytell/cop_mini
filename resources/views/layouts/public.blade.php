@php
    $whatsappDigits = preg_replace('/\D+/', '', (string) ($whatsappNumber ?? '')) ?? '';
    $accountUrl = $accountUrl ?? null;
    $publicLinks = [
        ['Home', route('home')],
        ['About', route('site.about')],
        ['Mission', route('site.mission')],
        ['Leadership', route('site.leadership')],
        ['How it works', route('site.how-it-works')],
        ['Investments', route('site.investments')],
        ['Membership', route('site.membership')],
        ['Referral program', route('site.referral-program')],
        ['News', route('news.index')],
        ['Events', route('events.index')],
        ['FAQ', route('faq')],
        ['Contact', route('contact')],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1f3a">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @include('layouts.partials.seo')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-cream font-sans text-ink" x-data="publicShell" :class="{ 'overflow-hidden': navOpen }" @keydown.escape.window="navOpen = false" @scroll.window="onScroll()">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-3">Skip to content</a>

    <header class="sticky top-0 z-40 border-b border-navy-900/10 bg-cream/95 backdrop-blur">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-3 px-4">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 min-w-0 items-center font-serif text-xl font-semibold tracking-tight text-navy-900">
                <span class="truncate">{{ $siteName }}</span>
            </a>
            <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
                <a href="{{ route('site.about') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">About</a>
                <a href="{{ route('site.how-it-works') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">How it works</a>
                <a href="{{ route('site.investments') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Investments</a>
                <a href="{{ route('site.membership') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Membership</a>
                <a href="{{ route('news.index') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">News</a>
                <a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Contact</a>
                @if ($accountUrl)
                    <a href="{{ $accountUrl }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Account</a>
                    <form method="POST" action="{{ route('logout') }}" class="ml-1">
                        @csrf
                        <button type="submit" class="btn-primary">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold hover:bg-white">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary ml-1">Join now</a>
                @endif
            </nav>
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300 lg:hidden" @click="navOpen = true" :aria-expanded="navOpen.toString()" aria-controls="public-drawer">
                <span class="sr-only">Open menu</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    <noscript>
        <nav class="border-b border-navy-900/10 bg-white px-4 py-3 lg:hidden" aria-label="Pages">
            <ul class="flex flex-col">
                @foreach ($publicLinks as [$label, $href])
                    <li><a class="inline-flex min-h-11 items-center font-semibold" href="{{ $href }}">{{ $label }}</a></li>
                @endforeach
                @if ($accountUrl)
                    <li><a class="inline-flex min-h-11 items-center font-semibold" href="{{ $accountUrl }}">Account</a></li>
                @else
                    <li><a class="inline-flex min-h-11 items-center font-semibold" href="{{ route('login') }}">Log in</a></li>
                    <li><a class="inline-flex min-h-11 items-center font-semibold" href="{{ route('register') }}">Join now</a></li>
                @endif
            </ul>
        </nav>
    </noscript>

    <div id="public-drawer" x-cloak x-show="navOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-ink/50" @click="navOpen = false"></div>
        <div class="absolute inset-y-0 right-0 flex w-[min(100%,20rem)] flex-col overflow-y-auto bg-cream shadow-xl">
            <div class="flex items-center justify-between px-4 py-3">
                <p class="font-serif text-lg font-semibold text-navy-900">Menu</p>
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white ring-1 ring-stone-300" @click="navOpen = false">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1 px-3 pb-8" aria-label="Mobile">
                @foreach ($publicLinks as [$label, $href])
                    <a href="{{ $href }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">{{ $label }}</a>
                @endforeach
                @if ($accountUrl)
                    <a href="{{ $accountUrl }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Account</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn-primary w-full">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary mt-2 w-full">Join now</a>
                @endif
            </nav>
        </div>
    </div>

    <main id="main">
        @yield('content')
    </main>

    <footer class="bg-navy-900 text-cream">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="font-serif text-2xl">{{ $siteName }}</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-cream/80">A member-owned cooperative. Contributions become units in a pool of real estate, gold, oil, and other holdings meant to stay steady.</p>
                @if ($officeAddress !== '')
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-cream/80">{{ $officeAddress }}</p>
                @endif
                <a href="mailto:{{ $contactEmail }}" class="mt-3 inline-flex min-h-11 items-center break-all text-sm font-semibold text-gold-400">{{ $contactEmail }}</a>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-400">Explore</p>
                <ul class="mt-3 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.about') }}">About us</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.mission') }}">Mission and vision</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.leadership') }}">Leadership</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.how-it-works') }}">How it works</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('gallery') }}">Gallery</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-400">Members</p>
                <ul class="mt-3 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.membership') }}">Units and pricing</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.referral-program') }}">Referral program</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('events.index') }}">Events</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('downloads') }}">Downloads</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('faq') }}">FAQ</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold-400">Legal</p>
                <ul class="mt-3 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.terms') }}">Terms and conditions</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.privacy') }}">Privacy policy</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('site.risk-disclosure') }}">Risk disclosure</a></li>
                    <li><a class="inline-flex min-h-11 items-center text-sm font-semibold" href="{{ route('contact') }}">Contact</a></li>
                </ul>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($socialLinks as $network => $url)
                        @if ($href = safe_url(is_string($url) ? $url : null))
                            <a href="{{ $href }}" class="inline-flex min-h-11 items-center rounded-full bg-white/10 px-4 text-sm font-semibold capitalize" rel="noopener noreferrer">{{ $network }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <p class="mx-auto max-w-6xl px-4 py-4 text-xs leading-5 text-cream/70">Units are a record of contributions. They are not a bank deposit, and returns are not guaranteed. Read the risk disclosure before you join.</p>
        </div>
    </footer>

    @if (strlen($whatsappDigits) >= 8)
        <a href="https://wa.me/{{ $whatsappDigits }}" class="fixed bottom-4 right-4 z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#128C7E] text-white shadow-lg" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.15 6.4 2.15 11.83c0 1.74.46 3.44 1.34 4.94L2 22l5.39-1.4a10.1 10.1 0 004.65 1.12h.01c5.46 0 9.89-4.4 9.89-9.84C21.94 6.4 17.5 2 12.04 2zm5.76 14.15c-.24.68-1.4 1.3-1.94 1.38-.5.07-1.12.1-1.81-.11-.41-.13-.95-.31-1.64-.61-2.88-1.25-4.76-4.15-4.9-4.34-.14-.2-1.16-1.54-1.16-2.94s.73-2.08 1-2.37c.24-.26.64-.38 1.02-.38.12 0 .23 0 .33.01.3.01.44.03.64.49.24.57.82 1.98.89 2.12.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.16-.29.36-.42.49-.14.13-.28.27-.12.53.16.26.72 1.18 1.54 1.91 1.06.95 1.95 1.24 2.23 1.38.28.14.44.12.6-.07.17-.19.7-.81.88-1.09.19-.28.37-.23.62-.14.26.09 1.62.76 1.9.9.28.14.46.21.53.32.07.12.07.68-.17 1.36z"/></svg>
        </a>
    @endif

    <button type="button" class="fixed bottom-20 right-4 z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-navy-900 text-cream shadow-lg" x-cloak x-show="showTop" @click="toTop()" aria-label="Back to top">
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 12l5-5 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <div class="fixed inset-x-4 bottom-4 z-40 max-w-md rounded-3xl bg-white p-4 shadow-xl ring-1 ring-stone-200 sm:left-4 sm:right-auto" x-data="cookieNotice" x-cloak x-show="open" role="dialog" aria-label="Cookie notice">
        <p class="text-sm leading-6 text-stone-700">We store a single preference so this notice stays closed. Read the <a class="inline-flex min-h-11 items-center font-semibold text-navy-800 underline" href="{{ route('site.privacy') }}">privacy policy</a>.</p>
        <button type="button" class="btn-primary mt-3" @click="accept()">Accept</button>
    </div>
</body>
</html>
