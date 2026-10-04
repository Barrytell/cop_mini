<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __invoke(Page $page): View
    {
        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
