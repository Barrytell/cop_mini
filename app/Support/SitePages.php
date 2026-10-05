<?php

declare(strict_types=1);

namespace App\Support;

use App\Modules\Cms\Models\Page;

class SitePages
{
    /**
     * @var array<string, string>
     */
    public const PATHS = [
        'about' => 'about',
        'mission' => 'mission',
        'leadership' => 'leadership',
        'how-it-works' => 'how-it-works',
        'investments' => 'investments',
        'real-estate' => 'investments/real-estate',
        'gold' => 'investments/gold',
        'oil-energy' => 'investments/oil-and-energy',
        'other-assets' => 'investments/other-assets',
        'membership' => 'membership',
        'referral-program' => 'referral-program',
        'terms' => 'terms',
        'privacy' => 'privacy',
        'risk-disclosure' => 'risk-disclosure',
    ];

    public static function url(Page $page): string
    {
        $path = self::PATHS[$page->slug] ?? 'pages/'.$page->slug;

        return url($path);
    }
}
