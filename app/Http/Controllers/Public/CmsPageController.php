<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\TeamMember;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function named(Request $request): View
    {
        $page = Page::query()->published()->where('slug', (string) $request->route('slug'))->firstOrFail();

        return $this->render($page);
    }

    public function show(Page $page): View
    {
        return $this->render($page);
    }

    private function render(Page $page): View
    {
        return view('pages.show', [
            'page' => $page,
            'team' => $page->template === 'team' ? TeamMember::query()->published()->get() : collect(),
            'faqs' => $page->template === 'faq' ? Faq::query()->published()->get() : collect(),
            'assets' => in_array($page->template, ['investments', 'asset'], true)
                ? Page::query()->published()->where('template', 'asset')->orderBy('sort_order')->get()
                : collect(),
            'unitPrice' => Money::present((string) setting('unit_price_usd', '0.01')),
            'minimum' => Money::present((string) setting('min_payment_usd', '10.00')),
            'referralBonus' => (int) setting('referral_bonus_units', 2),
        ]);
    }
}
