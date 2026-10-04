<?php

use App\Http\Controllers\Member\ActivationController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\PaymentController;
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
    });
