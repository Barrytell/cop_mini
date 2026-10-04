<?php

use App\Http\Controllers\AccountSuspendedController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Webhook\FlutterwaveWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/pages/{page:slug}', PageController::class)->name('pages.show');

Route::post('/webhooks/flutterwave', FlutterwaveWebhookController::class)
    ->middleware('throttle:webhooks')
    ->name('webhooks.flutterwave');

Route::middleware('auth')->group(function () {
    Route::get('/account/suspended', AccountSuspendedController::class)->name('account.suspended');
});

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
require __DIR__.'/admin.php';
