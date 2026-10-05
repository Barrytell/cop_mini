<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\HtmlString;

class CmsText
{
    /**
     * Turn plain CMS copy into escaped HTML.
     * A blank line starts a new block. "## " is a heading. "- " is a list item.
     */
    public static function toHtml(?string $body): HtmlString
    {
        $body = trim((string) $body);

        if ($body === '') {
            return new HtmlString('');
        }

        $html = '';
        $blocks = preg_split("/\n\s*\n/", str_replace(["\r\n", "\r"], "\n", $body)) ?: [];

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", trim($block))), fn (string $line): bool => $line !== ''));

            if ($lines === []) {
                continue;
            }

            if (str_starts_with($lines[0], '## ')) {
                $html .= '<h2 class="mt-8 font-serif text-2xl text-navy-900">'.e(substr($lines[0], 3)).'</h2>';
                array_shift($lines);
            }

            $list = [];
            $paragraph = [];

            $flushParagraph = function () use (&$html, &$paragraph): void {
                if ($paragraph === []) {
                    return;
                }

                $html .= '<p class="mt-4 text-base leading-7 text-stone-700">'.e(implode(' ', $paragraph)).'</p>';
                $paragraph = [];
            };

            $flushList = function () use (&$html, &$list): void {
                if ($list === []) {
                    return;
                }

                $html .= '<ul class="mt-4 list-disc space-y-2 pl-5 text-stone-700">';

                foreach ($list as $item) {
                    $html .= '<li>'.e($item).'</li>';
                }

                $html .= '</ul>';
                $list = [];
            };

            foreach ($lines as $line) {
                if (str_starts_with($line, '- ')) {
                    $flushParagraph();
                    $list[] = substr($line, 2);
                } else {
                    $flushList();
                    $paragraph[] = $line;
                }
            }

            $flushParagraph();
            $flushList();
        }

        return new HtmlString($html);
    }
}
