<?php

declare(strict_types=1);

namespace App\Support;

class MapEmbed
{
    public static function url(?string $url): ?string
    {
        $safe = safe_url($url);

        if ($safe === null || ! str_starts_with(strtolower($safe), 'https://')) {
            return null;
        }

        $host = strtolower((string) parse_url($safe, PHP_URL_HOST));
        $path = (string) parse_url($safe, PHP_URL_PATH);

        if (in_array($host, ['www.openstreetmap.org', 'openstreetmap.org'], true)) {
            return $safe;
        }

        if (in_array($host, ['www.google.com', 'maps.google.com'], true) && str_contains(strtolower($path), '/maps')) {
            return $safe;
        }

        return null;
    }
}
