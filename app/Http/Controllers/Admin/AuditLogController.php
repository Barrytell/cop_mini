<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', AuditLog::class);

        return view('admin.audit-logs', [
            'logs' => AuditLog::query()->with('user')->latest('created_at')->paginate(20),
        ]);
    }
}
