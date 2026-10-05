<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Banner;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BannerController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::BANNERS);

        return view('admin.banners.index', [
            'banners' => Banner::query()->orderBy('position')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->requirePermission(AdminPermission::BANNERS);

        return view('admin.banners.form', ['banner' => new Banner(['position' => 'home_hero', 'is_active' => true, 'sort_order' => 1])]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::BANNERS);
        $data = $this->validated($request);
        $data['image_path'] = $this->storeImage($request) ?? 'images/banners/estate.svg';
        $banner = Banner::query()->create($data);
        $audit->record($request->user(), 'admin.banner.created', $banner, null, $data, $request);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        $this->requirePermission(AdminPermission::BANNERS);

        return view('admin.banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::BANNERS);
        $data = $this->validated($request);
        if ($path = $this->storeImage($request)) {
            $data['image_path'] = $path;
        }
        $previous = $banner->only(['title', 'subtitle', 'position', 'sort_order', 'is_active', 'link_url']);
        $banner->fill($data)->save();
        $audit->record($request->user(), 'admin.banner.updated', $banner, $previous, $data, $request);

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function reorder(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::BANNERS);
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:banners,id'],
        ]);

        foreach (array_values($data['order']) as $index => $id) {
            Banner::query()->whereKey($id)->update(['sort_order' => $index + 1]);
        }

        $audit->record($request->user(), 'admin.banner.reordered', null, null, $data, $request);

        return back()->with('status', 'Banner order saved.');
    }

    public function destroy(Request $request, Banner $banner, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::BANNERS);
        $banner->delete();
        $audit->record($request->user(), 'admin.banner.deleted', $banner, null, ['id' => $banner->id], $request);

        return back()->with('status', 'Banner deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'position' => ['required', 'in:home_hero,member_dashboard'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image']);

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = Str::ulid().'.'.$file->getClientOriginalExtension();
        $dir = public_path('images/banners');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($dir, $name);

        return 'images/banners/'.$name;
    }
}
