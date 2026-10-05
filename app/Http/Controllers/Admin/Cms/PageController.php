<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Page;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::CONTENT);

        return view('admin.cms.pages.index', [
            'pages' => Page::query()->orderBy('sort_order')->orderBy('title')->paginate(30),
        ]);
    }

    public function edit(Page $page): View
    {
        $this->requirePermission(AdminPermission::CONTENT);

        return view('admin.cms.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash'],
            'template' => ['required', 'string', 'max:40'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:50000'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'nav_group' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['sometimes', 'boolean'],
            'show_in_nav' => ['sometimes', 'boolean'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['show_in_nav'] = $request->boolean('show_in_nav');
        $data['slug'] = Str::slug($data['slug']);
        $previous = $page->only(array_keys($data));
        $page->fill($data)->save();
        $audit->record($request->user(), 'admin.page.updated', $page, $previous, $data, $request);

        return redirect()->route('admin.cms.pages.index')->with('status', 'Page saved.');
    }
}
