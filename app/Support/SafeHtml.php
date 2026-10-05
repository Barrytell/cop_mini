<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\HtmlString;

class SafeHtml
{
    public static function from(?string $html): HtmlString
    {
        $html = trim((string) $html);

        if ($html === '') {
            return new HtmlString('');
        }

        $allowed = '<p><br><strong><b><em><i><ul><ol><li><a><h2><h3>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\son\w+\s*=\s*("|\').*?\1/i', '', $clean) ?? $clean;
        $clean = preg_replace_callback('/<a\s+([^>]+)>/i', function (array $matches): string {
            if (! preg_match('/href\s*=\s*("|\')(.*?)\1/i', $matches[1], $href)) {
                return '<a>';
            }

            $url = safe_url(html_entity_decode($href[2], ENT_QUOTES));

            return $url ? '<a href="'.e($url).'" rel="noopener noreferrer">' : '<a>';
        }, $clean) ?? $clean;

        return new HtmlString($clean);
    }
}
