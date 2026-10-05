<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\MeetingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Post;
use App\Modules\Cms\Models\Testimonial;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Units\Models\UnitLedgerEntry;
use App\Support\Money;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $news = Post::query()->published()->latest('published_at')->limit(3)->get();

        if ($news->isEmpty()) {
            $news = Announcement::query()->published()->latest('published_at')->limit(3)->get();
        }

        return view('home', [
            'banners' => Banner::query()->active()->where('position', 'home_hero')->orderBy('sort_order')->orderBy('id')->get(),
            'news' => $news,
            'meeting' => Meeting::query()
                ->where('status', MeetingStatus::Scheduled)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first(),
            'assets' => Page::query()->published()->where('template', 'asset')->orderBy('sort_order')->get(),
            'faqs' => Faq::query()->published()->limit(5)->get(),
            'testimonials' => Testimonial::query()->published()->get(),
            'unitPrice' => Money::present((string) setting('unit_price_usd', '0.01')),
            'minimum' => Money::present((string) setting('min_payment_usd', '10.00')),
            'referralBonus' => (int) setting('referral_bonus_units', 2),
            'stats' => [
                'members' => User::query()->where('role', UserRole::Member)->where('status', UserStatus::Active)->count(),
                'units' => (int) UnitLedgerEntry::query()->sum('units'),
                'assets' => Page::query()->published()->where('template', 'asset')->count(),
            ],
        ]);
    }
}
