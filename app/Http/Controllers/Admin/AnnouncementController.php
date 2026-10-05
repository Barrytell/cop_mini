<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Jobs\DispatchAnnouncement;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.announcements.index', [
            'announcements' => Announcement::query()->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.announcements.form', [
            'announcement' => new Announcement(['audience' => 'all', 'send_notification' => true]),
            'members' => User::query()->where('role', UserRole::Member)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['attachment_path'] = $this->storeAttachment($request);
        $announcement = Announcement::query()->create($data);
        $this->dispatchIfNeeded($announcement);
        $audit->record($request->user(), 'admin.announcement.created', $announcement, null, $announcement->only(['title', 'audience', 'is_published']), $request);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement saved.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.announcements.form', [
            'announcement' => $announcement,
            'members' => User::query()->where('role', UserRole::Member)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Announcement $announcement, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $previous = $announcement->only(['title', 'body', 'audience', 'is_published', 'is_pinned']);
        $data = $this->validated($request);
        if ($path = $this->storeAttachment($request)) {
            $data['attachment_path'] = $path;
        }
        $announcement->fill($data)->save();
        $this->dispatchIfNeeded($announcement);
        $audit->record($request->user(), 'admin.announcement.updated', $announcement, $previous, $announcement->only(['title', 'body', 'audience', 'is_published', 'is_pinned']), $request);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $announcement->delete();
        $audit->record($request->user(), 'admin.announcement.deleted', $announcement, null, ['id' => $announcement->id], $request);

        return back()->with('status', 'Announcement deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:20000'],
            'audience' => ['required', 'in:all,active,pending,selected'],
            'audience_user_ids' => ['nullable', 'array'],
            'audience_user_ids.*' => ['integer', 'exists:users,id'],
            'is_pinned' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'scheduled_for' => ['nullable', 'date'],
            'send_email' => ['sometimes', 'boolean'],
            'send_notification' => ['sometimes', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:4096'],
        ]);

        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['is_published'] = $request->boolean('is_published');
        $data['send_email'] = $request->boolean('send_email');
        $data['send_notification'] = $request->boolean('send_notification');
        $data['published_at'] = $data['is_published'] ? ($data['scheduled_for'] ?? now()) : null;
        unset($data['attachment']);

        return $data;
    }

    private function storeAttachment(Request $request): ?string
    {
        if (! $request->hasFile('attachment')) {
            return null;
        }

        $file = $request->file('attachment');
        $name = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('files/announcements'), $name);

        return 'files/announcements/'.$name;
    }

    private function dispatchIfNeeded(Announcement $announcement): void
    {
        if (! $announcement->is_published) {
            return;
        }

        if ($announcement->scheduled_for && $announcement->scheduled_for->isFuture()) {
            return;
        }

        DispatchAnnouncement::dispatch($announcement->id);
    }
}
