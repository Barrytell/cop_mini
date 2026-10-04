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

        if (! preg_match('#\Ahttps?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;
    }
}
