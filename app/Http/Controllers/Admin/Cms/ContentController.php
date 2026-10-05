<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\GalleryItem;
use App\Modules\Cms\Models\MenuItem;
use App\Modules\Cms\Models\Post;
use App\Modules\Cms\Models\PostCategory;
use App\Modules\Cms\Models\TeamMember;
use App\Modules\Cms\Models\Testimonial;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(string $type): View
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $model = $this->model($type);

        return view('admin.cms.content.index', [
            'type' => $type,
            'title' => $this->title($type),
            'items' => $model::query()->latest('id')->paginate(30),
            'columns' => $this->columns($type),
        ]);
    }

    public function create(string $type): View
    {
        $this->requirePermission(AdminPermission::CONTENT);

        return view('admin.cms.content.form', [
            'type' => $type,
            'title' => $this->title($type),
            'item' => $this->model($type)::query()->newModelInstance($this->defaults($type)),
            'fields' => $this->fields($type),
            'categories' => $type === 'posts' ? PostCategory::query()->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request, string $type, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $data = $this->validated($request, $type);
        $item = $this->model($type)::query()->create($data);
        $audit->record($request->user(), 'admin.cms.'.$type.'.created', $item, null, $data, $request);

        return redirect()->route('admin.cms.content.index', $type)->with('status', 'Saved.');
    }

    public function edit(string $type, int $id): View
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $item = $this->model($type)::query()->findOrFail($id);

        return view('admin.cms.content.form', [
            'type' => $type,
            'title' => $this->title($type),
            'item' => $item,
            'fields' => $this->fields($type),
            'categories' => $type === 'posts' ? PostCategory::query()->orderBy('name')->get() : collect(),
        ]);
    }

    public function update(Request $request, string $type, int $id, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $item = $this->model($type)::query()->findOrFail($id);
        $data = $this->validated($request, $type, $item);
        $previous = $item->only(array_keys($data));
        $item->fill($data)->save();
        $audit->record($request->user(), 'admin.cms.'.$type.'.updated', $item, $previous, $data, $request);

        return redirect()->route('admin.cms.content.index', $type)->with('status', 'Updated.');
    }

    public function destroy(Request $request, string $type, int $id, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::CONTENT);
        $item = $this->model($type)::query()->findOrFail($id);
        $item->delete();
        $audit->record($request->user(), 'admin.cms.'.$type.'.deleted', $item, null, ['id' => $id], $request);

        return back()->with('status', 'Deleted.');
    }

    /**
     * @return class-string<Model>
     */
    private function model(string $type): string
    {
        return match ($type) {
            'posts' => Post::class,
            'categories' => PostCategory::class,
            'faqs' => Faq::class,
            'testimonials' => Testimonial::class,
            'team' => TeamMember::class,
            'gallery' => GalleryItem::class,
            'downloads' => Download::class,
            'menus' => MenuItem::class,
            default => abort(404),
        };
    }

    private function title(string $type): string
    {
        return match ($type) {
            'posts' => 'Blog posts',
            'categories' => 'Post categories',
            'faqs' => 'FAQ',
            'testimonials' => 'Testimonials',
            'team' => 'Team members',
            'gallery' => 'Gallery',
            'downloads' => 'Downloads',
            'menus' => 'Menus',
            default => 'Content',
        };
    }

    /**
     * @return list<string>
     */
    private function columns(string $type): array
    {
        return match ($type) {
            'posts' => ['title', 'slug', 'is_published'],
            'categories' => ['name', 'slug'],
            'faqs' => ['question', 'is_published', 'sort_order'],
            'testimonials' => ['name', 'role', 'is_published'],
            'team' => ['name', 'role', 'is_published'],
            'gallery' => ['title', 'is_published', 'sort_order'],
            'downloads' => ['title', 'file_path', 'is_published'],
            'menus' => ['label', 'location', 'url', 'is_active'],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private function fields(string $type): array
    {
        return match ($type) {
            'posts' => [
                'title' => 'text', 'slug' => 'text', 'post_category_id' => 'category', 'excerpt' => 'textarea',
                'body' => 'textarea', 'cover_path' => 'text', 'is_published' => 'checkbox', 'published_at' => 'datetime',
                'meta_title' => 'text', 'meta_description' => 'textarea',
            ],
            'categories' => ['name' => 'text', 'slug' => 'text', 'description' => 'textarea'],
            'faqs' => ['question' => 'text', 'answer' => 'textarea', 'sort_order' => 'number', 'is_published' => 'checkbox'],
            'testimonials' => ['name' => 'text', 'role' => 'text', 'location' => 'text', 'quote' => 'textarea', 'sort_order' => 'number', 'is_published' => 'checkbox'],
            'team' => ['name' => 'text', 'role' => 'text', 'bio' => 'textarea', 'image_path' => 'text', 'sort_order' => 'number', 'is_published' => 'checkbox'],
            'gallery' => ['title' => 'text', 'caption' => 'textarea', 'image_path' => 'text', 'sort_order' => 'number', 'is_published' => 'checkbox'],
            'downloads' => ['title' => 'text', 'description' => 'textarea', 'file_path' => 'text', 'file_size' => 'number', 'sort_order' => 'number', 'is_published' => 'checkbox'],
            'menus' => ['label' => 'text', 'url' => 'text', 'location' => 'text', 'sort_order' => 'number', 'is_active' => 'checkbox'],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(string $type): array
    {
        return match ($type) {
            'posts' => ['is_published' => false, 'published_at' => now()],
            'faqs', 'testimonials', 'team', 'gallery', 'downloads' => ['is_published' => true, 'sort_order' => 0],
            'menus' => ['is_active' => true, 'location' => 'footer', 'sort_order' => 0],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, string $type, ?Model $item = null): array
    {
        $rules = match ($type) {
            'posts' => [
                'title' => ['required', 'string', 'max:160'],
                'slug' => ['required', 'string', 'max:160', 'alpha_dash'],
                'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
                'excerpt' => ['required', 'string', 'max:500'],
                'body' => ['required', 'string', 'max:50000'],
                'cover_path' => ['nullable', 'string', 'max:255'],
                'is_published' => ['sometimes', 'boolean'],
                'published_at' => ['nullable', 'date'],
                'meta_title' => ['nullable', 'string', 'max:160'],
                'meta_description' => ['nullable', 'string', 'max:255'],
            ],
            'categories' => [
                'name' => ['required', 'string', 'max:120'],
                'slug' => ['required', 'string', 'max:120', 'alpha_dash'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            'faqs' => [
                'question' => ['required', 'string', 'max:255'],
                'answer' => ['required', 'string', 'max:5000'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_published' => ['sometimes', 'boolean'],
            ],
            'testimonials' => [
                'name' => ['required', 'string', 'max:120'],
                'role' => ['required', 'string', 'max:120'],
                'location' => ['nullable', 'string', 'max:120'],
                'quote' => ['required', 'string', 'max:2000'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_published' => ['sometimes', 'boolean'],
            ],
            'team' => [
                'name' => ['required', 'string', 'max:120'],
                'role' => ['required', 'string', 'max:120'],
                'bio' => ['required', 'string', 'max:5000'],
                'image_path' => ['nullable', 'string', 'max:255'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_published' => ['sometimes', 'boolean'],
            ],
            'gallery' => [
                'title' => ['required', 'string', 'max:160'],
                'caption' => ['nullable', 'string', 'max:500'],
                'image_path' => ['required', 'string', 'max:255'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_published' => ['sometimes', 'boolean'],
            ],
            'downloads' => [
                'title' => ['required', 'string', 'max:160'],
                'description' => ['nullable', 'string', 'max:500'],
                'file_path' => ['required', 'string', 'max:255'],
                'file_size' => ['required', 'integer', 'min:0'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_published' => ['sometimes', 'boolean'],
            ],
            'menus' => [
                'label' => ['required', 'string', 'max:120'],
                'url' => ['required', 'string', 'max:255'],
                'location' => ['required', 'in:header,footer'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            default => abort(404),
        };

        $data = $request->validate($rules);

        foreach (['is_published', 'is_active'] as $flag) {
            if (array_key_exists($flag, $this->fields($type))) {
                $data[$flag] = $request->boolean($flag);
            }
        }

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        return $data;
    }
}
