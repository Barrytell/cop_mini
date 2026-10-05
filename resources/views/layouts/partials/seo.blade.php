@php
    $documentTitle = trim($__env->yieldContent('title') ?: ($title ?? $siteName));
    if (! str_contains($documentTitle, (string) $siteName)) {
        $documentTitle .= ' · '.$siteName;
    }
    $documentDescription = trim($__env->yieldContent('meta_description') ?: 'Members of '.$siteName.' pool funds for real estate, gold, oil, and other holdings meant to stay steady.');
    $canonicalUrl = trim($__env->yieldContent('canonical') ?: url()->current());
    $robots = trim($__env->yieldContent('robots') ?: 'index, follow');
    $ogImage = trim($__env->yieldContent('og_image') ?: asset('images/og.jpg'));
    $sameAs = array_values(array_filter(array_map(
        fn ($url) => is_string($url) ? safe_url($url) : null,
        $socialLinks ?? [],
    )));
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => config('app.url'),
        'email' => $contactEmail,
        'description' => 'A cooperative network whose members pool resources for real estate, gold, oil, and other stable assets.',
        'logo' => asset('icons/icon-512.png'),
    ];
    if ($sameAs !== []) {
        $organization['sameAs'] = $sameAs;
    }
@endphp
<title>{{ $documentTitle }}</title>
<meta name="description" content="{{ $documentDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $documentTitle }}">
<meta property="og:description" content="{{ $documentDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $documentTitle }}">
<meta name="twitter:description" content="{{ $documentDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
