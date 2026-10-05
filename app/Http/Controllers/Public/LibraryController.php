<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\GalleryItem;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Testimonial;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function faq(): View
    {
        return view('faq.index', [
            'page' => Page::query()->published()->where('slug', 'faq')->first(),
            'faqs' => Faq::query()->published()->get(),
        ]);
    }

    public function testimonials(): View
    {
        return view('testimonials.index', [
            'page' => Page::query()->published()->where('slug', 'testimonials')->first(),
            'testimonials' => Testimonial::query()->published()->get(),
        ]);
    }

    public function gallery(): View
    {
        return view('gallery.index', [
            'page' => Page::query()->published()->where('slug', 'gallery')->first(),
            'items' => GalleryItem::query()->published()->get(),
        ]);
    }

    public function downloads(): View
    {
        return view('downloads.index', [
            'page' => Page::query()->published()->where('slug', 'downloads')->first(),
            'downloads' => Download::query()->published()->get(),
        ]);
    }
}
