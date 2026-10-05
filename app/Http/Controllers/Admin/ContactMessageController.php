<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\ContactMessage;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(Request $request): View
    {
        $this->requirePermission(AdminPermission::SUPPORT);

        $query = ContactMessage::query()->latest('id');

        if ($request->query('unread') === '1') {
            $query->where('is_read', false);
        }

        return view('admin.contact.index', [
            'messages' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function show(ContactMessage $contact, AuditLogService $audit): View
    {
        $this->requirePermission(AdminPermission::SUPPORT);

        if (! $contact->is_read) {
            $contact->forceFill(['is_read' => true, 'read_at' => now()])->save();
            $audit->record(auth()->user(), 'admin.contact.read', $contact, null, ['id' => $contact->id]);
        }

        return view('admin.contact.show', ['message' => $contact]);
    }

    public function destroy(Request $request, ContactMessage $contact, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SUPPORT);
        $contact->delete();
        $audit->record($request->user(), 'admin.contact.deleted', $contact, null, ['id' => $contact->id], $request);

        return redirect()->route('admin.contact.index')->with('status', 'Message deleted.');
    }
}
