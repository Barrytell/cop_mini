<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Post;
use App\Support\SitePages;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('news.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('events.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('faq'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('contact'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('testimonials'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('gallery'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('downloads'), 'lastmod' => now()->toAtomString()],
        ];

        foreach (Page::query()->published()->orderBy('slug')->get() as $page) {
            $urls[] = [
                'loc' => SitePages::url($page),
                'lastmod' => $page->updated_at?->toAtomString(),
            ];
        }

        foreach (Post::query()->published()->orderByDesc('published_at')->get() as $post) {
            $urls[] = [
                'loc' => route('news.show', $post),
                'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
            ];
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
