<?php

use App\Http\Controllers\Member\ActivationController;
use App\Http\Controllers\Member\AnnouncementController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\LedgerController;
use App\Http\Controllers\Member\MeetingController;
use App\Http\Controllers\Member\NotificationController;
use App\Http\Controllers\Member\PaymentController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\ReferralController;
use App\Http\Controllers\Member\SupportTicketController;
use App\Http\Controllers\Member\UnitPurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:member', 'account.not_suspended'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {
        Route::get('/activate', ActivationController::class)->name('activate');
        Route::get('/units/buy', UnitPurchaseController::class)->middleware('member.active')->name('units.buy');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/quote', [PaymentController::class, 'quote'])->middleware('throttle:payments')->name('payments.quote');
        Route::post('/payments', [PaymentController::class, 'store'])->middleware('throttle:payments')->name('payments.store');
        Route::get('/payments/callback', [PaymentController::class, 'callback'])->middleware('throttle:payments')->name('payments.callback');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('/dashboard', DashboardController::class)->middleware('member.active')->name('dashboard');

        Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
        Route::get('/referrals/qr', [ReferralController::class, 'qr'])->name('referrals.qr');

        Route::get('/units', [LedgerController::class, 'index'])->name('ledger.index');
        Route::get('/units/export', [LedgerController::class, 'export'])->name('ledger.export');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'password'])->middleware('throttle:auth')->name('profile.password');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

        Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
        Route::post('/meetings/{meeting}/rsvp', [MeetingController::class, 'rsvp'])->name('meetings.rsvp');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

        Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
        Route::get('/support/new', [SupportTicketController::class, 'create'])->name('support.create');
        Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:support')->name('support.store');
        Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:support')->name('support.reply');
    });
