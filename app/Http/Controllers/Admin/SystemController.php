<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\WebhookEvent;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SystemController extends Controller
{
    use AuthorizesAdminPermission;

    public function health(): View
    {
        $this->requirePermission(AdminPermission::SYSTEM);

        $failedJobs = DB::table('failed_jobs')->orderByDesc('id')->limit(20)->get();
        $pendingJobs = DB::table('jobs')->count();
        $lastReconcile = cache('payments.last_reconcile_at');

        return view('admin.system.health', [
            'pendingJobs' => $pendingJobs,
            'failedJobs' => $failedJobs,
            'webhooks' => WebhookEvent::query()->latest('id')->limit(30)->get(),
            'lastReconcile' => $lastReconcile,
            'queueConnection' => config('queue.default'),
            'maintenance' => (bool) setting('maintenance_mode', false),
        ]);
    }

    public function backup(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SYSTEM);

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $stamp = now()->format('Ymd-His');

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $name = 'backup-'.$stamp.'.sqlite';
            $database = config("database.connections.{$connection}.database");
            File::copy($database, $dir.DIRECTORY_SEPARATOR.$name);
        } else {
            $name = 'backup-'.$stamp.'.sql';
            File::put(
                $dir.DIRECTORY_SEPARATOR.$name,
                "-- minimini.org backup placeholder generated at ".now()->toIso8601String()."\n-- Run mysqldump on the server for a full dump.\n",
            );
        }

        $audit->record($request->user(), 'admin.system.backup', null, null, ['file' => $name], $request);

        return back()->with('status', 'Backup file written to storage/app/backups/'.$name);
    }

    public function retryFailed(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SYSTEM);
        Artisan::call('queue:retry', ['id' => 'all']);
        $audit->record($request->user(), 'admin.system.queue_retry', null, null, ['result' => Artisan::output()], $request);

        return back()->with('status', 'Failed jobs were re-queued.');
    }
}
