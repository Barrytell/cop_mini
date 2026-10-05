<?php

declare(strict_types=1);

use App\Services\SettingsService;

if (! function_exists('setting')) {
    /**
     * Read a cached, admin-editable setting.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        if (func_num_args() > 1) {
            return app(SettingsService::class)->get($key, $default);
        }

        return app(SettingsService::class)->get($key);
    }
}

if (! function_exists('safe_url')) {
    /**
     * Allow only http(s) URLs through to an href.
     */
    function safe_url(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (! preg_match('#\Ahttps?://#i', $url) || str_contains($url, '\\') || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || isset($parts['user']) || isset($parts['pass']) || ($parts['host'] ?? '') === '') {
            return null;
        }

        return $url;
    }
}

if (! function_exists('public_file')) {
    /**
     * Turn a stored public-disk path into a URL.
     * Rejects schemes, traversal, and anything that is not a site file.
     */
    function public_file(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || preg_match('#\A[a-z][a-z0-9+.-]*:#i', $path)) {
            return null;
        }

        if (! preg_match('#\A[A-Za-z0-9][A-Za-z0-9_./-]*\z#', $path)) {
            return null;
        }

        return asset($path);
    }
}
