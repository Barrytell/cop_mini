<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\OutboundMessage;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Jobs\SendOutboundMessage;

class OutboundMessageController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.outbound.index', [
            'messages' => OutboundMessage::query()->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.outbound.form');
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $data = $request->validate([
            'channel' => ['required', 'in:email,sms'],
            'subject' => ['nullable', 'required_if:channel,email', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:10000'],
            'audience' => ['required', 'in:all,active,pending'],
        ]);

        $count = User::query()->where('role', UserRole::Member)
            ->when($data['audience'] === 'active', fn ($q) => $q->where('status', UserStatus::Active))
            ->when($data['audience'] === 'pending', fn ($q) => $q->where('status', UserStatus::Pending))
            ->count();

        $message = OutboundMessage::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
            'recipient_count' => $count,
            'status' => 'queued',
        ]);

        SendOutboundMessage::dispatch($message->id);
        $audit->record($request->user(), 'admin.outbound.queued', $message, null, $data + ['recipient_count' => $count], $request);

        return redirect()->route('admin.outbound.index')->with('status', 'Message queued for '.$count.' recipients.');
    }
}
