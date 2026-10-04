<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Payments\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'memberCount' => User::query()->where('role', UserRole::Member)->count(),
            'pendingCount' => User::query()->where('status', UserStatus::Pending)->count(),
            'activeCount' => User::query()->where('role', UserRole::Member)->where('status', UserStatus::Active)->count(),
            'successfulPayments' => Payment::query()->where('status', PaymentStatus::Successful)->count(),
            'recentMembers' => User::query()->where('role', UserRole::Member)->latest()->limit(8)->get(),
            'recentAudits' => AuditLog::query()->with('user')->latest('created_at')->limit(8)->get(),
        ]);
    }
}
