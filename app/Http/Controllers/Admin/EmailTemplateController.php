<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\EmailTemplate;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.email-templates.form', ['template' => $emailTemplate]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:20000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $previous = $emailTemplate->only(['name', 'subject', 'body', 'is_active']);
        $emailTemplate->fill($data)->save();
        $audit->record($request->user(), 'admin.email_template.updated', $emailTemplate, $previous, $data, $request);

        return redirect()->route('admin.email-templates.index')->with('status', 'Template saved.');
    }
}
