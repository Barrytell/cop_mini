<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\Cms\ContentController;
use App\Http\Controllers\Admin\Cms\PageController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OutboundMessageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TotpController;
use App\Http\Controllers\Admin\UnitSettingController;
use App\Http\Middleware\StopImpersonation;
use App\Models\User;
use App\Support\AdminPermission;
use Illuminate\Support\Facades\Route;

Route::post('/admin/impersonate/stop', fn () => abort(500))
    ->middleware(['auth', StopImpersonation::class])
    ->name('admin.impersonate.stop');

Route::middleware(['auth', 'role:admin,super_admin', 'account.not_suspended', 'admin.totp'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/totp/setup', [TotpController::class, 'setup'])->name('totp.setup');
        Route::post('/totp/confirm', [TotpController::class, 'confirm'])->name('totp.confirm');
        Route::get('/totp/challenge', [TotpController::class, 'challenge'])->name('totp.challenge');
        Route::post('/totp/verify', [TotpController::class, 'verify'])->name('totp.verify');
        Route::post('/totp/disable', [TotpController::class, 'disable'])->name('totp.disable');

        Route::get('/', DashboardController::class)
            ->middleware('admin.permission:'.AdminPermission::DASHBOARD)
            ->name('dashboard');

        Route::middleware('admin.permission:'.AdminPermission::MEMBERS)->group(function () {
            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
            Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
            Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::post('/members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');
            Route::post('/members/{member}/suspend', [MemberController::class, 'suspend'])->name('members.suspend');
            Route::post('/members/{member}/unsuspend', [MemberController::class, 'unsuspend'])->name('members.unsuspend');
            Route::post('/members/{member}/reset-password', [MemberController::class, 'resetPassword'])->name('members.reset-password');
            Route::post('/members/{member}/units', [MemberController::class, 'adjustUnits'])->name('members.units');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::post('/members/{member}/restore', [MemberController::class, 'restore'])->name('members.restore');
            Route::post('/members/{member}/impersonate', [MemberController::class, 'impersonate'])->name('members.impersonate');
        });

        Route::middleware('admin.permission:'.AdminPermission::PAYMENTS)->group(function () {
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
            Route::post('/payments/{payment}/reverify', [PaymentController::class, 'reverify'])->name('payments.reverify');
            Route::post('/payments/{payment}/mark-successful', [PaymentController::class, 'markSuccessful'])->name('payments.mark-successful');
            Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        });

        Route::middleware('admin.permission:'.AdminPermission::SETTINGS)->group(function () {
            Route::get('/unit-settings', [UnitSettingController::class, 'edit'])->name('units.edit');
            Route::put('/unit-settings', [UnitSettingController::class, 'update'])->name('units.update');
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('admin.permission:'.AdminPermission::REFERRALS)->group(function () {
            Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
            Route::post('/referrals/{referral}/reward', [ReferralController::class, 'reward'])->name('referrals.reward');
            Route::post('/referrals/{referral}/reverse', [ReferralController::class, 'reverse'])->name('referrals.reverse');
        });

        Route::middleware('admin.permission:'.AdminPermission::COMMUNICATIONS)->group(function () {
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
            Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

            Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
            Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
            Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
            Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
            Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
            Route::get('/meetings/{meeting}/rsvps', [MeetingController::class, 'rsvps'])->name('meetings.rsvps');
            Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

            Route::get('/outbound', [OutboundMessageController::class, 'index'])->name('outbound.index');
            Route::get('/outbound/create', [OutboundMessageController::class, 'create'])->name('outbound.create');
            Route::post('/outbound', [OutboundMessageController::class, 'store'])->name('outbound.store');

            Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
            Route::get('/email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
            Route::put('/email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        });

        Route::middleware('admin.permission:'.AdminPermission::CONTENT)->group(function () {
            Route::get('/cms/pages', [PageController::class, 'index'])->name('cms.pages.index');
            Route::get('/cms/pages/{page}/edit', [PageController::class, 'edit'])->name('cms.pages.edit');
            Route::put('/cms/pages/{page}', [PageController::class, 'update'])->name('cms.pages.update');

            Route::get('/cms/{type}', [ContentController::class, 'index'])->name('cms.content.index');
            Route::get('/cms/{type}/create', [ContentController::class, 'create'])->name('cms.content.create');
            Route::post('/cms/{type}', [ContentController::class, 'store'])->name('cms.content.store');
            Route::get('/cms/{type}/{id}/edit', [ContentController::class, 'edit'])->name('cms.content.edit');
            Route::put('/cms/{type}/{id}', [ContentController::class, 'update'])->name('cms.content.update');
            Route::delete('/cms/{type}/{id}', [ContentController::class, 'destroy'])->name('cms.content.destroy');
        });

        Route::middleware('admin.permission:'.AdminPermission::BANNERS)->group(function () {
            Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
            Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
            Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
            Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
            Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
            Route::post('/banners/reorder', [BannerController::class, 'reorder'])->name('banners.reorder');
            Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
        });

        Route::middleware('admin.permission:'.AdminPermission::SUPPORT)->group(function () {
            Route::get('/contact', [ContactMessageController::class, 'index'])->name('contact.index');
            Route::get('/contact/{contact}', [ContactMessageController::class, 'show'])->name('contact.show');
            Route::delete('/contact/{contact}', [ContactMessageController::class, 'destroy'])->name('contact.destroy');

            Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
            Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
            Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
            Route::post('/support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');
        });

        Route::middleware('admin.permission:'.AdminPermission::ADMINS)->group(function () {
            Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
            Route::get('/admins/create', [AdminUserController::class, 'create'])->name('admins.create');
            Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
            Route::get('/admins/{admin}/edit', [AdminUserController::class, 'edit'])->name('admins.edit');
            Route::put('/admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
        });

        Route::get('/audit-logs', AuditLogController::class)
            ->middleware('admin.permission:'.AdminPermission::SYSTEM)
            ->name('audit.index');

        Route::middleware('admin.permission:'.AdminPermission::SYSTEM)->group(function () {
            Route::get('/system', [SystemController::class, 'health'])->name('system.health');
            Route::post('/system/backup', [SystemController::class, 'backup'])->name('system.backup');
            Route::post('/system/retry-failed', [SystemController::class, 'retryFailed'])->name('system.retry-failed');
        });

        Route::get('/reports', [ReportController::class, 'index'])
            ->middleware('admin.permission:'.AdminPermission::REPORTS)
            ->name('reports.index');
    });

Route::bind('member', function (string $value) {
    return User::withTrashed()
        ->where('role', UserRole::Member)
        ->whereKey($value)
        ->firstOrFail();
});

Route::bind('admin', function (string $value) {
    return User::query()
        ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
        ->whereKey($value)
        ->firstOrFail();
});
