<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Cms\Models\Page;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'banners' => Banner::query()->active()->where('position', 'home_hero')->orderBy('sort_order')->orderBy('id')->get(),
            'announcements' => Announcement::query()->published()->latest('published_at')->limit(3)->get(),
            'meeting' => Meeting::query()
                ->where('status', MeetingStatus::Scheduled)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first(),
            'pages' => Page::query()->published()->orderBy('title')->get(),
            'unitPrice' => (string) setting('unit_price_usd', '0.01'),
            'minimum' => (string) setting('min_payment_usd', '10.00'),
        ]);
    }
}
